<?php

namespace go1\util;

use Doctrine\DBAL\Connection;

class GroupHelper
{
    public static function isItemOf(Connection $db, string $entityType, int $entityId, int $groupId, int $status = GroupItemStatus::ACTIVE): bool
    {
        $sql = 'SELECT 1 FROM social_group_item WHERE entity_type = ? AND entity_id = ? AND group_id = ? AND status = ?';
        return $db->fetchColumn($sql, [$entityType, $entityId, $groupId, $status]) ?  true : false;
    }

    public static function canAccess(Connection $db, int $userId, int $groupID): bool
    {
        return static::isItemOf($db, 'user', $userId, $groupID);
    }
}
