<?php

namespace go1\util\award;

use Doctrine\DBAL\Connection;
use go1\util\DB;

class AwardItemEnrolmentHelper
{
    public static function load(Connection $db, int $awardItemEnrolmentId)
    {
        $awardItemEnrolments = static::loadMultiple($db, [$awardItemEnrolmentId]);

        return $awardItemEnrolments ? $awardItemEnrolments[0] : null;
    }

    public static function loadMultiple(Connection $db, array $awardItemEnrolmentIds)
    {
        $q = $db->createQueryBuilder();

        return $q
            ->select('*')
            ->from('award_item_enrolment')
            ->where($q->expr()->in('id', ':ids'))
            ->setParameter('ids', $awardItemEnrolmentIds, DB::INTEGERS)
            ->execute()
            ->fetchAll(DB::OBJ);
    }

    public static function parent(Connection $db, $awardItemEnrolment)
    {
        $params = [
            ':entity_id' => $awardItemEnrolment->award_id,
            ':user_id'   => $awardItemEnrolment->user_id,
        ];

        return $db
            ->executeQuery('SELECT * FROM award_item_enrolment WHERE entity_id = :entity_id AND user_id = :user_id', $params)
            ->fetch(DB::OBJ);
    }
}
