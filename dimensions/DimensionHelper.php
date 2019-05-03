<?php

namespace go1\util\dimensions;

use Doctrine\DBAL\Connection;
use go1\util\DB;

class DimensionHelper
{
    public static function load(Connection $go1, int $id)
    {
        return ($dimensions = static::loadMultiple($go1, [$id])) ? $dimensions[0] : false;
    }

    public static function loadMultiple(Connection $go1, array $ids)
    {
        $ids = array_map('intval', $ids);
        $dimensions = !$ids ? [] : $go1
            ->executeQuery('SELECT * FROM dimensions where id IN (?)', [$ids], [DB::INTEGERS])
            ->fetchAll(DB::OBJ);
        return $dimensions;
    }

    public static function loadAllForType(Connection $go1, int $type)
    {
            return $go1
                ->executeQuery('SELECT * FROM dimensions where type = ?', [$type], [DB::INTEGER])
                ->fetchAll(DB::OBJ);
    }
}
