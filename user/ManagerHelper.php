<?php

namespace go1\util\user;

use Doctrine\DBAL\Connection;
use go1\util\edge\EdgeHelper;
use go1\util\edge\EdgeTypes;
use PDO;

class ManagerHelper
{
    public static function isManagerOfUser(Connection $go1, string $portalName, int $managerUserId, int $studentId): bool
    {
        # From instance & user ID, we find account ID.
        $studentAccountId = 'SELECT u.mail FROM gc_user u WHERE u.id = ?';
        $studentAccountId = 'SELECT a.id FROM gc_user a WHERE a.instance = ? AND mail = (' . $studentAccountId . ')';
        $studentAccountId = (int) $go1->fetchColumn($studentAccountId, [$portalName, $studentId]);
        if (!$studentAccountId) {
            return false;
        }

        return EdgeHelper::hasLink($go1, EdgeTypes::HAS_MANAGER, $studentAccountId, $managerUserId);
    }

    public static function isManagerUser(Connection $go1, int $managerAccountId, string $instance): bool
    {
        if (!$roleId = UserHelper::roleId($go1, Roles::MANAGER, $instance)) {
            return false;
        }

        return EdgeHelper::hasLink($go1, EdgeTypes::HAS_ROLE, $managerAccountId, $roleId);
    }

    public static function userManagerIds(Connection $go1, int $accountId): array
    {
        $sql = 'SELECT ro.target_id FROM gc_ro ro ';
        $sql .= 'WHERE ro.source_id = ? AND ro.type = ?';

        return $go1
            ->executeQuery($sql, [$accountId, EdgeTypes::HAS_MANAGER])
            ->fetchAll(PDO::FETCH_COLUMN);
    }
}
