<?php

namespace go1\util\dimensions;

use Doctrine\DBAL\Connection;
use Assert\Assert;
use go1\util\DB;

class DimensionHelper
{
    public static function load(Connection $go1, int $id)
    {
        Assert::lazy()
            ->that($id, 'id')->numeric()
            ->verifyNow();

        return ($dimensions = static::loadMultiple($go1, [$id])) ? $dimensions[0] : false;
    }

    public static function loadMultiple(Connection $go1, array $ids)
    {
        $ids = array_map('intval', $ids);
        $dimensions = !$ids ? [] : $db
            ->executeQuery('SELECT * FROM dimensions where id IN (?)', [$ids], [DB::INTEGERS])
            ->fetchAll(DB::OBJ);
        return $dimensions;
    }

    public static function loadAllForType(Connection $go1, int $type)
    {
        Assert::lazy()
            ->that($type, 'type')->numeric()
            ->verifyNow();

        return $db->execQuery('SELECT * FROM dimensions where type = ?', [$type], [DB::INTEGER])->fetch(DB::OBJ);
    }
}
