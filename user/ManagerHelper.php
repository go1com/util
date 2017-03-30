<?php

namespace go1\util\user;

use Doctrine\DBAL\Connection;
use go1\util\edge\EdgeHelper;
use go1\util\edge\EdgeTypes;

class ManagerHelper
{
    public static function isManager(Connection $db, string $instance, int $managerUserId, int $userId): bool
    {
        # From instance & manager user ID, we find manager account ID.
        $managerAccountId = 'SELECT u.id from gc_user u WHERE u.id = ?';
        $managerAccountId = 'SELECT account.id FROM gc_user account WHERE account.instance = ? AND (' . $managerAccountId . ')';
        $managerAccountId = $db->fetchColumn($managerAccountId, [$instance, $managerUserId]);
        if (!$managerAccountId) {
            return false;
        }

        return EdgeHelper::hasLink($db, EdgeTypes::HAS_MANAGER, $userId, $managerAccountId);
    }
}
