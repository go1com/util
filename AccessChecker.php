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

    /**
     * @param Request $req
     * @param string  $portalName
     * @return null|bool|stdClass
     */
    public function isPortalAdmin(Request $req, $portalName)
    {
        if (!$user = $this->validUser($req)) {
            return null;
        }

        if ($this->isAccountsAdmin($req)) {
            return 1;
        }

        $accounts = isset($user->accounts) ? $user->accounts : [];
        foreach ($accounts as &$account) {
            if ($portalName === $account->instance) {
                if (!empty($account->roles) && in_array('administrator', $account->roles)) {
                    return $account;
                }
            }
        }

        return false;
    }

    public function isPortalTutor(Request $req, $portalName, $role = 'Tutor')
    {
        if ($this->isPortalAdmin($req, $portalName)) {
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

    public function isPortalManager(Request $req, $portalName)
    {
        return $this->isPortalTutor($req, $portalName, 'Manager');
    }

    public function isAccountsAdmin(Request $req)
    {
        if (!$user = $this->validUser($req)) {
            return null;
        }

        return in_array('Admin on #Accounts', isset($user->roles) ? $user->roles : []) ? $user : false;
    }

    public function validUser(Request $req, $instanceName = null)
    {
        $payload = $req->get('jwt.payload');
        if ($payload && !empty($payload->object->type) && ('user' === $payload->object->type)) {
            $user = &$payload->object->content;
            $user = !empty($user->mail) ? $user : null;
        }

        if (!empty($user)) {
            if (!$instanceName || empty($user->instance)) {
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

        return $this->validUser($req) ? static::ACCESS_AUTHENTICATED : static::ACCESS_PUBLIC;
    }
}
