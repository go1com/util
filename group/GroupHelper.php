<?php

namespace go1\util\group;

use Doctrine\DBAL\Connection;
use go1\util\AccessChecker;
use go1\util\DB;
use go1\util\lo\LoHelper;
use go1\util\note\NoteHelper;
use go1\util\portal\PortalHelper;
use go1\util\user\UserHelper;
use PDO;
use Symfony\Component\HttpFoundation\Request;
use stdClass;

class GroupHelper
{
    public static function load(Connection $db, int $id)
    {
        $sql = 'SELECT * FROM social_group WHERE id = ?';

        $group = $db->executeQuery($sql, [$id])->fetch(DB::OBJ);
        if ($group) {
            self::format($group);
        }

        return $group;
    }

    public static function loadMultiple(Connection $db, array $ids)
    {
        $groups = [];
        $sql = 'SELECT * FROM social_group WHERE id IN (?) ORDER BY id DESC';

        $query = $db->executeQuery($sql, [$ids], [Connection::PARAM_INT_ARRAY]);
        while ($group = $query->fetch(DB::OBJ)) {
            self::format($group);
            $groups[] = $group;
        }

        return $groups;
    }

    public static function findItems(Connection $db, int $groupId, string $entityType = null, $limit = 50, $offset = 0, $all = false)
    {
        while (true) {
            $qb = $db->createQueryBuilder();
            $qb->select('*')
                ->from('social_group_item', 'item')
                ->where('status = :status')
                ->setParameter(':status', GroupItemStatus::ACTIVE)
                ->andWhere('group_id = :groupId')
                ->setParameter(':groupId', $groupId);
            $entityType && $qb
                ->andWhere('entity_type = :entityType')
                ->setParameter(':entityType', $entityType);
            $items = $qb
                ->setFirstResult($offset)
                ->setMaxResults($limit)
                ->execute()
                ->fetchAll(DB::OBJ);
            if ($items) {
                foreach ($items as $item) {
                    yield $item;
                }
            }

            $offset += $limit;
            if (!$items || !$all) {
                break;
            }
        }
    }

    public static function isItemOf(Connection $db, string $entityType, int $entityId, int $groupId, int $status = GroupItemStatus::ACTIVE): bool
    {
        $sql = 'SELECT 1 FROM social_group_item WHERE entity_type = ? AND entity_id = ? AND group_id = ? AND status = ?';

        return $db->fetchColumn($sql, [$entityType, $entityId, $groupId, $status]) ? true : false;
    }

    public static function canAccess(Connection $db, int $userId, int $groupID): bool
    {
        return static::isItemOf($db, GroupItemTypes::USER, $userId, $groupID);
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

    public static function userGroups(Connection $db, int $userId)
    {
        $sql = 'SELECT g.title FROM social_group g ';
        $sql .= 'INNER JOIN social_group_item gi ON g.id = gi.group_id ';
        $sql .= 'WHERE gi.entity_type = ? ';
        $sql .= 'AND gi.entity_id = ?';

        return $db->executeQuery($sql, [GroupItemTypes::USER, $userId])->fetchAll(PDO::FETCH_COLUMN);
    }

    public static function getEntityId(Connection $go1, Connection $dbNote, Connection $dbSocial, $entityType, $entityId, $instance = '')
    {
        $validEntity = false;
        $id = $entityId;

        switch ($entityType) {
            case GroupItemTypes::PORTAL:
                $portalEntity = PortalHelper::load($go1, $entityId);
                $validEntity = is_object($portalEntity);
                break;

            case GroupItemTypes::USER:
                $target = (array) UserHelper::load($go1, $entityId);
                if (!empty($target) && $instance) {
                    $id = static::getAccountId($go1, $target, $instance);
                    $validEntity = true;
                }
                break;

            case GroupItemTypes::LO:
                $lo = LoHelper::load($go1, $entityId);
                $validEntity = is_object($lo);
                break;

            case GroupItemTypes::NOTE:
                $note = NoteHelper::loadByUUID($dbNote, $entityId);
                if (is_object($note)) {
                    $id = $note->id;
                    $validEntity = true;
                }

                break;

            case GroupItemTypes::GROUP:
                $group = GroupHelper::load($dbSocial, $entityId);
                $validEntity = is_object($group);

                break;
        }

        return $validEntity ? $id : 0;
    }

    public static function isPremium(stdClass $group)
    {
        $check = $group->data->premium ?? false;

        return $check ? true : false;
    }

    public static function isMarketplace(stdClass $group)
    {
        $check = $group->data->marketplace ?? 0;

        return $check ? true : false;
    }

    public static function format(stdClass &$group)
    {
        $group->data = is_scalar($group->data) ? json_decode($group->data) : $group->data;

        if (isset($group->data->description)) {
            $group->description = $group->data->description;
            unset($group->data->description);
        }
    }
}
