<?php

namespace go1\util\user;

use Doctrine\DBAL\Connection;
use go1\util\edge\EdgeHelper;
use go1\util\edge\EdgeTypes;
use PDO;

class ManagerHelper
{
    public static function isManagerOfUser(Connection $db, string $instance, int $managerUserId, int $studentId): bool
    {
        # From instance & user ID, we find account ID.
        $studentAccountId = 'SELECT u.mail FROM gc_user u WHERE u.id = ?';
        $studentAccountId = 'SELECT a.id FROM gc_user a WHERE a.instance = ? AND mail = (' . $studentAccountId . ')';
        $studentAccountId = (int) $db->fetchColumn($studentAccountId, [$instance, $studentId]);
        if (!$studentAccountId) {
            return false;
        }

        return EdgeHelper::hasLink($db, EdgeTypes::HAS_MANAGER, $studentAccountId, $managerUserId);
    }

    public static function isManagerUser(Connection $db, int $managerAccountId, string $instance): bool
    {
        if (!$roleId = UserHelper::roleId($db, Roles::MANAGER, $instance)) {
            return false;
        }

        return EdgeHelper::hasLink($db, EdgeTypes::HAS_ROLE, $managerAccountId, $roleId);
    }

    public static function userManagerIds(Connection $db, int $userId): array
    {
        $sql = 'SELECT ro.target_id FROM gc_ro ro ';
        $sql .= 'WHERE ro.source_id = ? AND ro.type = ?';

        return $db->executeQuery($sql, [$userId, EdgeTypes::HAS_MANAGER])->fetchAll(PDO::FETCH_COLUMN);
    }
}
