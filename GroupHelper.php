<?php

namespace go1\util;

use Doctrine\DBAL\Connection;

class GroupHelper
{
    const ITEM_TYPE_USER   = 'user';
    const ITEM_TYPE_LO     = 'lo';
    const ITEM_TYPE_PORTAL = 'instance';
    const ITEM_ALL         = [self::ITEM_TYPE_USER, self::ITEM_TYPE_LO, self::ITEM_TYPE_PORTAL];

    public static function isItemOf(Connection $db, string $entityType, int $entityId, int $groupId, int $status = GroupItemStatus::ACTIVE): bool
    {
        $sql = 'SELECT 1 FROM social_group_item WHERE entity_type = ? AND entity_id = ? AND group_id = ? AND status = ?';

        return $db->fetchColumn($sql, [$entityType, $entityId, $groupId, $status]) ? true : false;
    }

    public static function canAccess(Connection $db, int $userId, int $groupID): bool
    {
        return static::isItemOf($db, 'user', $userId, $groupID);
    }
}
