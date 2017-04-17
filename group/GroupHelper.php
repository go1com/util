<?php

namespace go1\util\group;

use Doctrine\DBAL\Connection;
use go1\util\AccessChecker;
use go1\util\DB;
use go1\util\user\UserHelper;
use PDO;
use Symfony\Component\HttpFoundation\Request;

class GroupHelper
{
    const ITEM_TYPE_USER   = 'user';
    const ITEM_TYPE_LO     = 'lo';
    const ITEM_TYPE_PORTAL = 'portal';
    const ITEM_TYPE_GROUP  = 'group';
    const ITEM_ALL         = [self::ITEM_TYPE_USER, self::ITEM_TYPE_LO, self::ITEM_TYPE_PORTAL, self::ITEM_TYPE_GROUP];

    public static function load(Connection $db, int $id)
    {
        $sql = 'SELECT * FROM social_group WHERE id = ?';

        return $db->executeQuery($sql, [$id])->fetch(DB::OBJ);
    }

    public static function isItemOf(Connection $db, string $entityType, int $entityId, int $groupId, int $status = GroupItemStatus::ACTIVE): bool
    {
        $sql = 'SELECT 1 FROM social_group_item WHERE entity_type = ? AND entity_id = ? AND group_id = ? AND status = ?';

        return $db->fetchColumn($sql, [$entityType, $entityId, $groupId, $status]) ? true : false;
    }

    public static function canAccess(Connection $db, int $userId, int $groupID): bool
    {
        return static::isItemOf($db, 'user', $userId, $groupID);
    }

    public static function groupAccess(int $groupUserId, int $userId, AccessChecker $accessChecker = null, Request $req = null, string $instance = ''): bool
    {
        if ($groupUserId == $userId) {
            return true;
        }

        if ($accessChecker instanceof AccessChecker) {
            if ($accessChecker->isAccountsAdmin($req)) {
                return true;
            }

            if ($instance && $accessChecker->isPortalAdmin($req, $instance)) {
                return true;
            }
        }

        return false;
    }

    public static function getAccountId(Connection $db, $user, string $instance): int
    {
        $users = [(array) $user];
        (new UserHelper)->attachRootAccount($db, $users, $instance);

        if (!isset($users[0]['root']['id'])) {
            return 0;
        }

        return $users[0]['root']['id'];
    }

    public function userGroups(Connection $db, int $userId)
    {
        $sql = 'SELECT g.title FROM social_group g ';
        $sql .= 'INNER JOIN social_group_item gi ON g.id = gi.group_id ';
        $sql .= 'WHERE gi.entity_type = ? ';
        $sql .= 'AND gi.entity_id = ?';

        return $db->executeQuery($sql, [self::ITEM_TYPE_USER, $userId])->fetchAll(PDO::FETCH_COLUMN);
    }
}
