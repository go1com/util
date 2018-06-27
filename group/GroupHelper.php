<?php

namespace go1\util\group;

use Doctrine\DBAL\Connection;
use go1\util\AccessChecker;
use go1\util\award\AwardHelper;
use go1\util\DB;
use go1\util\lo\LoHelper;
use go1\util\note\NoteHelper;
use go1\util\portal\PortalHelper;
use go1\util\user\UserHelper;
use PDO;
use stdClass;
use Symfony\Component\HttpFoundation\Request;

class GroupHelper
{
    const G_TYPE_PREMIUM     = 'premium';
    const G_TYPE_MARKETPLACE = 'marketplace';
    const G_TYPE_DEFAULT     = 'default';

    public static function load(Connection $db, int $id)
    {
        $groups = self::loadMultiple($db, [$id]);

        return $groups[0] ?? false;
    }

    public static function loadMultiple(Connection $db, array $ids)
    {
        $q = $db->executeQuery('SELECT * FROM social_group WHERE id IN (?) ORDER BY id DESC', [$ids], [Connection::PARAM_INT_ARRAY]);
        while ($group = $q->fetch(DB::OBJ)) {
            self::format($group);
            $groups[] = $group;
        }

        !empty($groups) && self::countMembers($db, $groups);

        return $groups ?? [];
    }

    public static function loadItem(Connection $db, int $id)
    {
        return $db->executeQuery('SELECT * FROM social_group_item WHERE id = ?', [$id])->fetch(DB::OBJ);
    }

    public static function loadItemByGroupAndEntity(Connection $db, int $groupId, string $entityType, int $entityId)
    {
        $sql = 'SELECT * FROM social_group_item WHERE group_id = ? AND entity_type = ? AND entity_id = ?';

        return $db->executeQuery($sql, [$groupId, $entityType, $entityId], [DB::INTEGER, DB::STRING, DB::INTEGER])->fetch(DB::OBJ);
    }

    public static function loadGroupByTitle(Connection $db, string $title, string $type = null)
    {
        $q = $db
            ->createQueryBuilder()
            ->select('id')
            ->from('social_group')
            ->where('title = :title')->setParameter(':title', $title);

        $type && $q
            ->andWhere('type = :type')
            ->setParameter(':type', $type);

        return ($id = $q->execute()->fetchColumn())
            ? self::load($db, $id)
            : false;
    }

    public static function hostGroupName(string $hostEntityType, int $hostEntityId, bool $marketplace): string
    {
        return $marketplace ? "go1:{$hostEntityType}:{$hostEntityId}:marketplace" : "go1:{$hostEntityType}:{$hostEntityId}";
    }

    public static function hostContentSharingGroup(Connection $db, string $hostEntityType, $hostEntityId, bool $marketplace = false)
    {
        return self::loadGroupByTitle(
            $db,
            self::hostGroupName($hostEntityType, $hostEntityId, $marketplace),
            GroupTypes::CONTENT_SHARING
        );
    }

    public static function instanceId(Connection $db, int $groupId)
    {
        static $instanceIds = [];

        if (isset($instanceIds[$groupId])) {
            return $instanceIds[$groupId];
        }

        if ($group = self::load($db, $groupId)) {
            $instanceIds[$groupId] = $group->instance_id;
        }

        return $instanceIds[$groupId] ?? null;
    }

    public static function findItems(Connection $db, int $groupId, string $entityType = null, $limit = 50, $offset = 0, $all = false)
    {
        while (true) {
            $continue = false;
            $q = $db->createQueryBuilder();
            $q->select('*')
              ->setFirstResult($offset)
              ->setMaxResults($limit)
              ->from('social_group_item', 'item')
              ->where('status = :status')->setParameter(':status', GroupItemStatus::ACTIVE)
              ->andWhere('group_id = :groupId')->setParameter(':groupId', $groupId);

            $entityType && $q->andWhere('entity_type = :entityType')->setParameter(':entityType', $entityType);
            $q = $q->execute();
            while ($item = $q->fetch(DB::OBJ)) {
                $continue = true;
                yield $item;
            }

            $offset += $limit;
            if (!$continue || !$all) {
                break;
            }
        }
    }

    public static function isItemOf(Connection $db, string $entityType, int $entityId, int $groupId, int $status = GroupItemStatus::ACTIVE)
    {
        $id = 'SELECT id FROM social_group_item WHERE entity_type = ? AND entity_id = ? AND group_id = ? AND status = ?';
        $id = $db->fetchColumn($id, [$entityType, $entityId, $groupId, $status]);

        return $id ? intval($id) : false;
    }

    public static function canAccess(Connection $go1, Connection $social, int $userId, int $groupId): bool
    {
        if (!$group = static::load($social, $groupId)) {
            return false;
        }

        if ($userId == $group->user_id) {
            return true;
        }

        if (!$instance = PortalHelper::nameFromId($go1, $group->instance_id)) {
            return false;
        }

        if (!$user = UserHelper::load($go1, $userId)) {
            return false;
        }

        if (!$account = UserHelper::loadByEmail($go1, $instance, $user->mail)) {
            return false;
        }

        return static::isItemOf($social, GroupItemTypes::USER, $account->id, $groupId);
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

        return $users[0]['root']['id'] ?? 0;
    }

    public static function userGroups(Connection $go1, Connection $social, int $portalId, int $accountId, string $accountsName)
    {
        $userId = UserHelper::userId($go1, $accountId, $accountsName);
        $memberGroupIds = $social
            ->executeQuery('SELECT group_id FROM social_group_item WHERE entity_type = ? AND entity_id = ?', [GroupItemTypes::USER, $accountId])
            ->fetchAll(DB::COL);

        return $social
            ->executeQuery(
                'SELECT title FROM social_group WHERE instance_id = ? AND `type` = ? AND (user_id = ? OR id IN (?))',
                [$portalId, GroupTypes::DEFAULT, $userId, $memberGroupIds],
                [DB::INTEGER, DB::STRING, DB::INTEGER, DB::INTEGERS])
            ->fetchAll(DB::COL);
    }

    public static function getEntityId(
        string $entityType,
        $entityId,
        $instance = '',
        Connection $go1 = null,
        Connection $dbNote = null,
        Connection $dbSocial = null,
        Connection $dbAward = null
    )
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
                    $entityId = static::getAccountId($go1, $target, $instance);
                    $validEntity = true;
                }
                break;

            case GroupItemTypes::LO:
                $lo = LoHelper::load($go1, $id);
                $validEntity = is_object($lo);
                break;

            case GroupItemTypes::NOTE:
                if ($dbNote) {
                    $note = NoteHelper::loadByUUID($dbNote, $entityId);
                    if (is_object($note)) {
                        $entityId = $note->id;
                        $validEntity = true;
                    }
                }
                break;

            case GroupItemTypes::GROUP:
                if ($dbSocial) {
                    $group = GroupHelper::load($dbSocial, $entityId);
                    $validEntity = is_object($group);
                }
                break;

            case GroupItemTypes::AWARD:
                if ($dbAward) {
                    $group = AwardHelper::load($dbAward, $entityId);
                    $validEntity = is_object($group);
                }
                break;
        }

        return $validEntity ? $entityId : 0;
    }

    public static function isContent(stdClass $group)
    {
        if (isset($group->type)) {
            return $group->type == GroupTypes::CONTENT;
        }

        return ($group->data->premium ?? false) ? true : false;
    }

    public static function isContentSharing(stdClass $group)
    {
        return $group->type == GroupTypes::CONTENT_SHARING;
    }

    public static function isPassiveContentSharing(stdClass $group)
    {
        return self::isContentSharing($group) && (strpos($group->title, 'marketplace') !== false);
    }

    public static function isContentPackage(stdClass $group)
    {
        if (isset($group->type)) {
            return $group->type == GroupTypes::CONTENT_PACKAGE;
        }

        return ($group->data->marketplace ?? 0) ? true : false;
    }

    /**
     * @deprecated
     */
    public static function isPremium(stdClass $group)
    {
        return self::isContent($group);
    }

    /**
     * @deprecated
     */
    public static function isMarketplace(stdClass $group)
    {
        return self::isContentPackage($group);
    }

    public static function isDefault(stdClass $group)
    {
        return $group->type == GroupTypes::DEFAULT;
    }

    public static function isSystem(stdClass $group)
    {
        return $group->type == GroupTypes::SYSTEM;
    }

    public static function format(stdClass &$group)
    {
        $group->data = is_scalar($group->data) ? json_decode($group->data) : $group->data;
        $group->description = $group->data->description ?? '';
        $group->image = $group->data->image ?? '';
    }

    public static function countMembers(Connection $db, array &$groups)
    {
        $ids = array_column($groups, 'id');
        $sql = 'SELECT group_id, COUNT(id) as count FROM social_group_item WHERE group_id IN (?) GROUP BY group_id';
        $query = $db->executeQuery($sql, [$ids], [Connection::PARAM_INT_ARRAY]);
        while ($item = $query->fetch(DB::OBJ)) {
            foreach ($groups as &$group) {
                if ($group->id == $item->group_id) {
                    $group->member_count = $item->count;
                }
            }
        }
    }

    public static function loadAssignment(Connection $db, int $assignId)
    {
        return $db->executeQuery('SELECT * FROM social_group_assign WHERE id = ?', [$assignId])->fetch(DB::OBJ);
    }

    public static function groupAssignments(Connection $db, int $groupId, array $options = [])
    {
        $q = $db->createQueryBuilder();
        $q
            ->select('*')
            ->from('social_group_assign')
            ->where('group_id = :groupId')
            ->andWhere('status = :status')
            ->setParameters([
                ':groupId' => $groupId,
                ':status'  => isset($options['status']) ? $options['status'] : GroupAssignStatuses::PUBLISHED,
            ]);

        isset($options['entityType']) && $q
            ->andWhere('entity_type = :entityType')
            ->setParameter('entityType', $options['entityType']);

        return $q->execute()->fetchAll(DB::OBJ);
    }

    public static function groupTypePermission(stdClass $group, Request $req)
    {
        $accessChecker = new AccessChecker;
        $access = self::isDefault($group) && ($accessChecker->validUser($req) ? true : false);

        if (!$access) {
            $access = $accessChecker->isAccountsAdmin($req) ? true : false;
        }

        return $access;
    }

    public static function hostIdFromGroupTitle(string $title)
    {
        return explode(":", $title)[2];
    }

    public static function hostTypeFromGroupTitle(string $title)
    {
        return explode(":", $title)[1];
    }

    public static function findGroupIdsByItem(Connection $db, string $entityType, int $entityId, int $offset = 0, int $limit = 50): array
    {
        $q = $db->createQueryBuilder();

        return $q->select('group_id')
                 ->from('social_group_item', 'item')
                 ->where('entity_type = ?')
                 ->andWhere('entity_id = ?')
                 ->andWhere('status = ?')
                 ->setParameters([$entityType, $entityId, GroupItemStatus::ACTIVE], [DB::STRING, DB::INTEGER, DB::INTEGER])
                 ->setFirstResult($offset)
                 ->setMaxResults($limit)
                 ->execute()
                 ->fetchAll(DB::COL);
    }

    public static function countGroupByItem(Connection $db, string $entityType, int $entityId): int
    {
        $q = $db->createQueryBuilder();

        return $q->select('count(group_id)')
                 ->from('social_group_item', 'item')
                 ->where('entity_type = ?')
                 ->andWhere('entity_id = ?')
                 ->andWhere('status = ?')
                 ->setParameters([$entityType, $entityId, GroupItemStatus::ACTIVE], [DB::STRING, DB::INTEGER, DB::INTEGER])
                 ->execute()
                 ->fetchColumn();
    }

    public static function isPortalSystemGroup(string $title)
    {
        $explode = explode(":", $title);
        if ((count($explode) == 3) && ($explode[0] == 'go1') && ($explode[1] == 'portal')) {
            return true;
        }

        return false;
    }

    public static function isMemberOfContentSharingGroup(Connection $db, int $loId, int $instanceId, bool $marketplace = null): bool
    {
        if (!is_null($marketplace)) {
            $hostGroup = self::hostContentSharingGroup($db, GroupItemTypes::LO, $loId, $marketplace);

            return $hostGroup && self::isItemOf($db, GroupItemTypes::PORTAL, $instanceId, $hostGroup->id);
        }

        if ($hostGroup = self::hostContentSharingGroup($db, GroupItemTypes::LO, $loId, true)) {
            if (self::isItemOf($db, GroupItemTypes::PORTAL, $instanceId, $hostGroup->id)) {
                return true;
            }
        }

        if ($hostGroup = self::hostContentSharingGroup($db, GroupItemTypes::LO, $loId, false)) {
            if (self::isItemOf($db, GroupItemTypes::PORTAL, $instanceId, $hostGroup->id)) {
                return true;
            }
        }

        return false;
    }

    public static function isAuthor(stdClass $group, int $userId): bool
    {
        return $group->user_id == $userId;
    }
}
