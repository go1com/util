<?php

namespace go1\util\award;

use Doctrine\DBAL\Connection;
use Exception;
use go1\util\DB;
use go1\util\edge\EdgeHelper;
use go1\util\edge\EdgeTypes;
use go1\util\lo\LoHelper;
use go1\util\Text;
use go1\util\text\Xss;
use HTMLPurifier;
use PDO;
use stdClass;

class AwardHelper
{
    public static function isEmbeddedPortalActive(stdClass $award): bool
    {
        $portal = $award->embedded->portal ?? null;

        return $portal ? $portal->status : true;
    }

    public static function getQuantityType($quantity)
    {
        if (is_null($quantity)) {
            return AwardQuantityTypes::COMPLETE_ANY;
        }

        if (!is_scalar($quantity)) {
            throw new Exception('Invalid award quantity type.');
        }

        $quantity = (float) $quantity;
        if (0.0 === $quantity) {
            return AwardQuantityTypes::TRACK_ONGOING;
        }

        if ($quantity > 0) {
            return AwardQuantityTypes::REACH_TARGET;
        }

        throw new Exception('Invalid award quantity type.');
    }

    public static function format(stdClass &$award, HTMLPurifier $html = null)
    {
        $award->id = intval($award->id);
        $award->revision_id = intval($award->revision_id);
        $award->instance_id = intval($award->instance_id);
        $award->user_id = intval($award->user_id);
        $award->title = trim(Xss::filter($award->title));
        $award->description = $html
            ? $html->purify(trim($award->description), LoHelper::descriptionPurifierConfig())
            : $award->description;
        $award->tags = Text::parseInlineTags((string) $award->tags);
        $award->locale = Text::parseInlineTags((string) $award->locale);

        $data = is_scalar($award->data) ? json_decode($award->data, true) : $award->data;
        $award->data = (object) (is_array($data)
            ? array_diff_key($data, array_flip(['avatar', 'roles']))
            : $data);
        $award->published = intval($award->published);
        $award->quantity = isset($award->quantity) ? (float) $award->quantity : null;
        $award->expire = ctype_digit($award->expire) ? (int) $award->expire : $award->expire;
        $award->created = intval($award->created);
    }

    public static function loadOrGetFromAwardEnrolmentEmbeddedData(Connection $db, stdClass $awardEnrolment)
    {
        return $awardEnrolment->embedded->award ?? self::load($db, $awardEnrolment->award_id);
    }

    public static function load(Connection $db, int $awardId, array $statuses = [])
    {
        $awards = static::loadMultiple($db, [$awardId], $statuses);

        return $awards ? $awards[0] : null;
    }

    public static function loadMultiple(Connection $db, array $awardIds, array $statuses = [])
    {
        $q = $db->createQueryBuilder();
        $q
            ->select('*')
            ->from('award_award')
            ->where($q->expr()->in('id', ':ids'))
            ->setParameter('ids', $awardIds, DB::INTEGERS);
        $statuses && $q
            ->andWhere($q->expr()->in('published', ':published'))
            ->setParameter('published', $statuses, DB::INTEGERS);
        $q = $q->execute();

        while ($award = $q->fetch(DB::OBJ)) {
            self::format($award);
            $awards[] = $award;
        }

        return $awards ?? [];
    }

    public static function loadByRevision(Connection $db, int $revisionId, array $statuses = [])
    {
        $awards = self::loadMultipleByRevision($db, [$revisionId], $statuses);

        return $awards ? $awards[0] : null;
    }

    public static function loadMultipleByRevision(Connection $db, array $revisionIds, array $statuses = [])
    {
        $q = $db->createQueryBuilder();
        $q
            ->select('*')
            ->from('award_award')
            ->where($q->expr()->in('revision_id', ':revisionIds'))
            ->setParameter('revisionIds', $revisionIds, DB::INTEGERS);
        $statuses && $q
            ->andWhere($q->expr()->in('published', ':published'))
            ->setParameter('published', $statuses, DB::INTEGERS);
        $q = $q->execute();

        while ($award = $q->fetch(DB::OBJ)) {
            self::format($award);
            $awards[] = $award;
        }

        return $awards ?? [];
    }

    public static function loadItems(Connection $db, array $awardItemIds)
    {
        return $db
            ->executeQuery('SELECT * FROM award_item WHERE id IN (?)', [$awardItemIds], [DB::INTEGERS])
            ->fetchAll(DB::OBJ);
    }

    public static function loadItem(Connection $db, int $awardItemId)
    {
        return ($items = static::loadItems($db, [$awardItemId]))
            ? $items[0]
            : false;
    }

    public static function loadManualItem(Connection $db, int $manualItemId, $status = AwardStatuses::PUBLISHED)
    {
        return ($manualItems = static::loadManualItems($db, [$manualItemId], $status))
            ? $manualItems[0]
            : false;
    }

    public static function loadManualItems(Connection $db, array $manualItemIds, $status = AwardStatuses::PUBLISHED)
    {
        $manualItems = [];
        $manualItemQuery = $db
            ->executeQuery('SELECT * FROM award_item_manual WHERE id IN (?) AND published = ?', [$manualItemIds, $status], [DB::INTEGERS, DB::INTEGER]);

        while ($manualItem = $manualItemQuery->fetch(DB::OBJ)) {
            if (!$manualItem->data = json_decode($manualItem->data)) {
                unset($manualItem->data);
            }

            if (!empty($manualItem->categories)) {
                $manualItem->categories = Text::parseInlineTags($manualItem->categories);
            }

            $manualItem->pass = !empty($manualItem->pass);
            $manualItems[] = $manualItem;
        }

        return $manualItems;
    }

    public static function loadAchievements(Connection $db, array $achievementIds)
    {
        return $db
            ->executeQuery('SELECT * FROM award_achievement WHERE id IN (?)', [$achievementIds], [DB::INTEGERS])
            ->fetchAll(DB::OBJ);
    }

    public static function loadAchievement(Connection $db, int $achievementId)
    {
        return ($achievements = static::loadAchievements($db, [$achievementId]))
            ? $achievements[0]
            : false;
    }

    public static function loadAchievementBy(Connection $db, int $awardItemId, int $userId)
    {
        return ($achievements = self::loadAchievementsBy($db, [$awardItemId], [$userId]))
            ? $achievements[0]
            : null;
    }

    public static function loadAchievementsBy(Connection $db, array $awardItemIds, array $userIds = [])
    {
        $q = $db->createQueryBuilder();
        $q
            ->select('*')
            ->from('award_achievement')
            ->where($q->expr()->in('award_item_id', ':award_item_id'))
            ->setParameter(':award_item_id', $awardItemIds, DB::INTEGERS);
        $userIds && $q
            ->andWhere($q->expr()->in('user_id', ':user_id'))
            ->setParameter(':user_id', $userIds, DB::INTEGERS);

        return $q->execute()->fetchAll(DB::OBJ);
    }

    public static function loadEnrolments(Connection $db, array $awardEnrolmentIds)
    {
        return $db
            ->executeQuery('SELECT * FROM award_enrolment WHERE id IN (?)', [$awardEnrolmentIds], [DB::INTEGERS])
            ->fetchAll(DB::OBJ);
    }

    public static function loadEnrolment(Connection $db, int $awardEnrolmentId)
    {
        return ($enrolments = static::loadEnrolments($db, [$awardEnrolmentId])) ? $enrolments[0] : false;
    }

    public static function loadEnrolmentBy(Connection $db, int $awardId, int $userId, int $instanceId)
    {
        return $db
            ->executeQuery('SELECT * FROM award_enrolment WHERE award_id = ? AND user_id = ? AND instance_id = ?', [$awardId, $userId, $instanceId])
            ->fetch(DB::OBJ);
    }

    public static function countEnrolment(Connection $db, int $awardId)
    {
        return $db->fetchColumn('SELECT COUNT(*) FROM award_enrolment WHERE award_id = ?', [$awardId]);
    }

    public static function id2revisionId(Connection $db, int $awardId)
    {
        return $db->fetchColumn('SELECT revision_id FROM award_award WHERE id = ?', [$awardId]);
    }

    public static function revisionId2Id(Connection $db, int $awardRevisionId)
    {
        return $db->fetchColumn('SELECT id FROM award_award WHERE revision_id = ?', [$awardRevisionId]);
    }

    public static function assessorIds(Connection $go1, int $loId): array
    {
        return EdgeHelper
            ::select('target_id')
            ->get($go1, [$loId], [], [EdgeTypes::AWARD_ASSESSOR], PDO::FETCH_COLUMN);
    }

    public static function awardParentIds(Connection $db, array $awardIds)
    {
        $revisionIds = $db->executeQuery('SELECT award_revision_id FROM award_item WHERE type = ? AND entity_id IN (?)', [AwardItemTypes::AWARD, $awardIds], [DB::STRING, DB::INTEGERS])->fetchAll(DB::COL);
        $awardIds = [];
        foreach ($revisionIds as $revisionId) {
            if ($awardId = self::revisionId2Id($db, $revisionId)) {
                $awardIds[] = $awardId;
            }
        }

        return $awardIds;
    }
}
