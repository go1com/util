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
}
