<?php

namespace go1\util\lo;

use Doctrine\DBAL\Connection;
use go1\util\AccessChecker;
use go1\util\DB;
use go1\util\edge\EdgeTypes;
use go1\util\portal\PortalChecker;
use go1\util\portal\PortalHelper;
use stdClass;
use Symfony\Component\HttpFoundation\Request;

class LoChecker
{
    private function loData(stdClass $lo)
    {
        if (empty($lo->data)) {
            return [];
        }

        return is_scalar($lo->data) ? json_decode($lo->data, true) : (is_array($lo->data) ? $lo->data : (is_object($lo->data) ? (array) $lo->data : []));
    }

    public function isModuleAuthor(Connection $db, int $moduleId, int $userId): bool
    {
        $courseId = 'SELECT source_id FROM gc_ro WHERE type IN (?) AND target_id = ?';
        $courseId = $db->fetchColumn($courseId, [[EdgeTypes::HAS_MODULE, EdgeTypes::HAS_ELECTIVE_LO], $moduleId], 0, [DB::INTEGERS, DB::INTEGER]);
        
        return $courseId ? $this->isAuthor($db, $courseId, $userId) : false;
    }

    public function isAuthor(Connection $db, int $loId, int $userId)
    {
        $sql = 'SELECT 1 FROM gc_ro WHERE source_id = ? AND type = ? AND target_id = ?';

        return $db->fetchColumn($sql, [$loId, EdgeTypes::HAS_AUTHOR_EDGE, $userId]) ? true : false;
    }

    public function isAuthorOnPortal(Connection $db, $instanceIdOrTitle, int $userId)
    {
        $instanceId = is_numeric($instanceIdOrTitle) ? $instanceIdOrTitle : PortalHelper::idFromName($db, $instanceIdOrTitle);

        $sql = 'SELECT 1 FROM gc_ro ro';
        $sql .= '  INNER JOIN gc_lo lo ON ro.source_id = lo.id AND lo.instance_id = ?';
        $sql .= '  WHERE ro.type = ? AND ro.target_id = ?';

        return $db->fetchColumn($sql, [$instanceId, EdgeTypes::HAS_AUTHOR_EDGE, $userId]) ? true : false;
    }

    public function manualPayment(stdClass $lo)
    {
        $data = $this->loData($lo);

        return isset($data[LoHelper::MANUAL_PAYMENT]) ? ($data[LoHelper::MANUAL_PAYMENT] ? true : false) : false;
    }

    public function manualPaymentRecipient(stdClass $lo)
    {
        $data = $this->loData($lo);

        return isset($data[LoHelper::MANUAL_PAYMENT_RECIPIENT]) ? $data[LoHelper::MANUAL_PAYMENT_RECIPIENT] : '';
    }

    public function singleLi(stdClass $li)
    {
        $data = $this->loData($li);

        return isset($data['single_li']) ? ($data['single_li'] ? true : false) : false;
    }

    public function allowReEnrol(stdClass $lo)
    {
        $data = $this->loData($lo);

        return isset($data[LoHelper::ENROLMENT_RE_ENROL])
            ? ($data[LoHelper::ENROLMENT_RE_ENROL] ? true : false)
            : LoHelper::ENROLMENT_RE_ENROL_DEFAULT;
    }

    public function allowEnrolment(stdClass $lo)
    {
        $data = $this->loData($lo);

        return empty($data[LoHelper::ENROLMENT_ALLOW]) ? LoHelper::ENROLMENT_ALLOW_DEFAULT : $data[LoHelper::ENROLMENT_ALLOW];
    }

    public function canCreate(Connection $db, string $instanceName, Request $req): bool
    {
        $accessChecker = new AccessChecker();
        if ($accessChecker->isPortalTutor($req, $instanceName)) {
            return true;
        }

        $portalChecker = new PortalChecker();
        $portal = $portalChecker->load($db, $instanceName);
        if ($portal && $portalChecker->allowPublicWriting($portal)) {
            return true;
        }

        return false;
    }

    public function canUpdate(Connection $db, int $id, string $instance, Request $req)
    {
        $access = new AccessChecker;

        if ($access->isPortalTutor($req, $instance)) {
            return true;
        }

        if ($user = $access->validUser($req)) {
            if ($this->isAuthor($db, $id, $user->id)) {
                return true;
            }
        }
    }

    public function access(AccessChecker $accessChecker, Request $req, string $instanceName)
    {
        return $accessChecker->isPortalTutor($req, $instanceName) || $accessChecker->isPortalManager($req, $instanceName);
    }
}
