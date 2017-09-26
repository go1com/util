<?php

namespace go1\util\award;

use Doctrine\DBAL\Connection;
use go1\util\DB;
use go1\util\lo\LoHelper;
use go1\util\Text;
use go1\util\text\Xss;
use HTMLPurifier;
use stdClass;

class AwardHelper
{
    public static function format(stdClass &$award, HTMLPurifier $html = null)
    {
        $award->id          = intval($award->id);
        $award->revision_id = intval($award->revision_id);
        $award->instance_id = intval($award->instance_id);
        $award->user_id     = intval($award->user_id);
        $award->title       = trim(Xss::filter($award->title));
        $award->description = $html
            ? $html->purify(trim($award->description), LoHelper::descriptionPurifierConfig())
            : $award->description;
        $award->tags        = Text::parseInlineTags((string) $award->tags);
        $award->locale      = Text::parseInlineTags((string) $award->locale);

        $data             = is_scalar($award->data) ? json_decode($award->data, true) : $award->data;
        $award->data      = (object) (is_array($data)
            ? array_diff_key($data, array_flip(['avatar', 'roles']))
            : $data);
        $award->published = intval($award->published);
        $award->quantity  = isset($award->quantity) ? (float) $award->quantity : null;
        $award->expire    = ctype_digit($award->expire) ? (int) $award->expire : $award->expire;
        $award->created   = intval($award->created);
    }

    public static function load(Connection $db, int $awardId, $statuses = [AwardStatuses::PUBLISHED, AwardStatuses::UNPUBLISHED])
    {
        $awards = static::loadMultiple($db, [$awardId], $statuses);

        return $awards ? $awards[0] : null;
    }

    public static function loadMultiple(Connection $db, array $awardIds, $statuses = [AwardStatuses::PUBLISHED, AwardStatuses::UNPUBLISHED])
    {
        $awards = $db
            ->executeQuery('SELECT * FROM award_award WHERE id IN (?) AND published IN (?)', [$awardIds, $statuses], [DB::INTEGERS, DB::INTEGERS])
            ->fetchAll(DB::OBJ);

        if ($awards) {
            foreach ($awards as &$award) {
                static::format($award);
            }
        }

        return $awards;
    }

    public static function loadByRevision(Connection $db, int $revisionId)
    {
        $award = $db
            ->executeQuery('SELECT * FROM award_award WHERE revision_id = ?', [$revisionId])
            ->fetch(DB::OBJ);
        $award && static::format($award);

        return $award;
    }

    public static function loadItem(Connection $db, int $awardItemId)
    {
        return $db
            ->executeQuery('SELECT * FROM award_item WHERE id = ?', [$awardItemId])
            ->fetch(DB::OBJ);
    }

    public static function loadManualItem(Connection $db, int $awardManualItemId, $status = AwardStatuses::PUBLISHED)
    {
        $awardManualItem = $db
            ->executeQuery('SELECT * FROM award_item_manual WHERE id = ? AND published = ?', [$awardManualItemId, $status])
            ->fetch(DB::OBJ);

        if ($awardManualItem) {
            if (!$awardManualItem->data = json_decode($awardManualItem->data)) {
                unset($awardManualItem->data);
            }
        }

        return $awardManualItem;
    }

    public static function loadAchievement(Connection $db, int $achievementId)
    {
        return $db
            ->executeQuery('SELECT * FROM award_achievement WHERE id = ?', [$achievementId])
            ->fetch(DB::OBJ);
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

    public static function loadEnrolment(Connection $db, int $awardEnrolmentId)
    {
        return $db
            ->executeQuery('SELECT * FROM award_enrolment WHERE id = ?', [$awardEnrolmentId])
            ->fetch(DB::OBJ);
    }
}
