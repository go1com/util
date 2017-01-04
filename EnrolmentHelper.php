<?php

namespace go1\util;

use Doctrine\DBAL\Connection;

class EnrolmentHelper
{
    /**
     * @param \Doctrine\DBAL\Connection $db
     * @param int $id
     * @return bool|mixed
     */
    public static function load(Connection $db, int $id)
    {
        return ($enrolments = static::loadMultiple($db, [$id])) ? $enrolments[0] : false;
    }


    /**
     * @param \Doctrine\DBAL\Connection $db
     * @param array $ids
     * @return array
     */
    public static function loadMultiple(Connection $db, array $ids): array
    {
        return $db
          ->executeQuery('SELECT * FROM gc_enrolment WHERE id IN (?)', [$ids], [Connection::PARAM_INT_ARRAY])
          ->fetchAll(DB::OBJ);
    }

    /**
     * @param \Doctrine\DBAL\Connection $db
     * @param $parentLoId
     * @return array
     */
    public static function loadByParentLo(Connection $db, int $parentLoId): array
    {
        return $db
          ->executeQuery('SELECT * FROM gc_enrolment WHERE parent_lo_id = ?', [$parentLoId])
          ->fetchAll(DB::OBJ);
    }
}
