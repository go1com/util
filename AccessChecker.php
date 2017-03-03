<?php

namespace go1\util;

use Doctrine\DBAL\Connection;
use PDO;
use stdClass;
use Symfony\Component\HttpFoundation\Request;

class AccessChecker
{
    const ACCESS_PUBLIC        = 0;
    const ACCESS_AUTHENTICATED = 100;
    const ACCESS_ADMIN         = 200;
    const ACCESS_ROOT          = 300;
    const ACCESS_OWNER         = 400;

    /**
     * @param Request $req
     * @param string  $instance
     * @return null|bool|stdClass
     */
    public function isPortalAdmin(Request $req, $instance)
    {
        if (!$user = $this->validUser($req)) {
            return null;
        }

        if ($this->isAccountsAdmin($req)) {
            return 1;
        }

        $accounts = isset($user->accounts) ? $user->accounts : [];
        foreach ($accounts as &$account) {
            if ($instance === $account->instance) {
                if (!empty($account->roles) && in_array(Roles::ADMIN, $account->roles)) {
                    return $account;
                }
            }
        }

        return false;
    }

    public function isPortalTutor(Request $req, $portalName, $role = Roles::TUTOR, bool $strict = true)
    {
        if ($strict && $this->isPortalAdmin($req, $portalName)) {
            return 1;
        }

        if (!$user = $this->validUser($req)) {
            return null;
        }

        $accounts = isset($user->accounts) ? $user->accounts : [];
        foreach ($accounts as &$account) {
            if ($portalName === $account->instance) {
                if (!empty($account->roles) && in_array($role, $account->roles)) {
                    return $account;
                }
            }
        }

        return false;
    }

    public function isPortalManager(Request $req, $portalName, bool $strict = true)
    {
        return $this->isPortalTutor($req, $portalName, Roles::MANAGER, $strict);
    }

    public function isAccountsAdmin(Request $req)
    {
        if (!$user = $this->validUser($req)) {
            return null;
        }

        return in_array(Roles::ROOT, isset($user->roles) ? $user->roles : []) ? $user : false;
    }

    public function validUser(Request $req, $instanceName = null)
    {
        $payload = $req->get('jwt.payload');
        if ($payload && !empty($payload->object->type) && ('user' === $payload->object->type)) {
            $user = &$payload->object->content;
            $user = !empty($user->mail) ? $user : null;
        }

        if (!empty($user)) {
            if (!$instanceName || empty($user->instance) || ($user->instance == $instanceName)) {
                return $user;
            }

            $accounts = isset($user->accounts) ? $user->accounts : [];
            foreach ($accounts as $account) {
                if ($instanceName == $account->instance) {
                    return $account;
                }
            }
        }

        return false;
    }

    public function isOwner(Request $req, $profileId)
    {
        if (!$user = $this->validUser($req)) {
            return false;
        }

        return $user->profile_id == $profileId;
    }

    public function hasAccount(Request $req, $portalName)
    {
        if (!$user = $this->validUser($req)) {
            return false;
        }

        if ($this->isPortalTutor($req, $portalName)) {
            return true;
        }

        $accounts = isset($user->accounts) ? $user->accounts : [];
        foreach ($accounts as &$account) {
            if ($portalName === $account->instance) {
                return true;
            }
        }

        return false;
    }

    public function isStudentManager(Connection $db, Request $req, $studentMail, $portalName, $hasManagerCode = 504)
    {
        if (!$user = $this->validUser($req)) {
            return null;
        }

        if (isset($user->mail) && $studentMail && !empty($user->accounts)) {
            foreach ($user->accounts as &$account) {
                if ($portalName == $account->instance) {
                    $isManager = $db->fetchColumn(
                        'SELECT 1 FROM gc_ro'
                        . ' WHERE type = ?'
                        . '   AND source_id = (SELECT id FROM gc_user WHERE instance = ? AND mail = ?)'
                        . '   AND target_id = (SELECT id FROM gc_user WHERE instance = ? AND mail = ?)',
                        [$hasManagerCode, $portalName, $studentMail, $portalName, $user->mail],
                        0,
                        [PDO::PARAM_INT, PDO::PARAM_STR, PDO::PARAM_STR, PDO::PARAM_STR, PDO::PARAM_STR]
                    );
                    if ($isManager) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    public function accessLevel(Request $req, $instance = null)
    {
        if ($this->isAccountsAdmin($req)) {
            return static::ACCESS_ROOT;
        }

        if ($instance && $this->isPortalAdmin($req, $instance)) {
            return static::ACCESS_ADMIN;
        }

        if (!$user = $this->validUser($req)) {
            return static::ACCESS_PUBLIC;
        }

        // THE LOGIC is not yet stable, only applying for #ECK for now
        if (($entityType = $req->attributes->get('entityType')) && ($entityId = $req->attributes->get('entityId'))) {
            if (0 === strpos($entityType, 'user')) {
                if ($user->id == $entityId) {
                    return static::ACCESS_OWNER;
                }
            }
        }

        return static::ACCESS_AUTHENTICATED;
    }

    public function isMasquerading(Request $req)
    {
        $payload = $req->get('jwt.payload');

        return ($payload && !empty($payload->object->content->masquerading)) ? true : false;
    }

    public static function isAssessor(
        Connection $db,
        int $courseId,
        int $assessorId,
        int $studentProfileId = null,
        Request $req = null): bool
    {
        $checker = new self;

        if ($checker->isAccountsAdmin($req)) {
            return true;
        }

        if ((new LoChecker)->isAuthor($db, $courseId, $assessorId)) {
            return true;
        }

        // Is portal admin
        $instance = PortalHelper::titleFromLoId($db, $courseId);
        if ($instance && $checker->isPortalAdmin($req, $instance)) {
            return true;
        }

        if ($studentProfileId) {
            if ($enrolmentId = EnrolmentHelper::enrolmentId($db, $courseId, $studentProfileId)) {
                if (EdgeHelper::hasLink($db, EdgeTypes::HAS_TUTOR_ENROLMENT_EDGE, $assessorId, $enrolmentId)) {
                    return true;
                }
            }
        }

        return EdgeHelper::hasLink($db, EdgeTypes::COURSE_ASSESSOR, $courseId, $assessorId);
    }
}
