<?php

namespace go1\util\award;

use Doctrine\DBAL\Connection;
use go1\util\DB;
use go1\util\edge\EdgeHelper;
use go1\util\edge\EdgeTypes;
use PDO;

class AwardEnrolmentHelper
{
    public static function assessorIds(Connection $db, int $enrolmentId): array
    {
        return EdgeHelper
            ::select('source_id')
            ->get($db, [], [$enrolmentId], [EdgeTypes::HAS_AWARD_TUTOR_ENROLMENT_EDGE], PDO::FETCH_COLUMN);
    }

    public static function loadMultiple(Connection $db, array $ids): array
    {
        return $db->executeQuery('SELECT * FROM award_enrolment WHERE id IN (?)', [$ids], [Connection::PARAM_INT_ARRAY])
            ->fetchAll(DB::OBJ);
    }

    /**
     * @return object|false
     */
    public static function load(Connection $db, int $id)
    {
        $awardEnrolments = static::loadMultiple($db, [$id]);
        return $awardEnrolments ? $awardEnrolments[0] : false;
    }
}
