<?php

namespace go1\util\user;

use Doctrine\DBAL\Connection;
use go1\util\edge\EdgeHelper;
use go1\util\edge\EdgeTypes;

class ManagerHelper
{
    public static function isManager(Connection $db, string $instance, int $managerUserId, int $userId): bool
    {
        # From instance & user ID, we find account ID.
        $accountId = 'SELECT u.mail FROM gc_user u WHERE u.id = ?';
        $accountId = 'SELECT a.id FROM gc_user a WHERE a.instance = ? AND mail = (' . $accountId . ')';
        $accountId = (int) $db->fetchColumn($accountId, [$instance, $userId]);
        if (!$accountId) {
            return false;
        }

        return EdgeHelper::hasLink($db, EdgeTypes::HAS_MANAGER, $accountId, $managerUserId);
    }
}
