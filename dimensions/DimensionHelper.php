<?php

namespace go1\util\dimensions;

use Doctrine\DBAL\Connection;
use go1\util\DB;
use go1\util\lo\LoAttributeTypes;

class DimensionHelper
{
    public static function load(Connection $db, int $id)
    {
        return ($dimensions = static::loadMultiple($db, [$id])) ? $dimensions[0] : false;
    }

    public static function loadMultiple(Connection $db, array $ids)
    {
        $ids = array_map('intval', $ids);
        $dimensions = !$ids ? [] : $db
            ->executeQuery('SELECT * FROM dimensions where id IN (?)', [$ids], [DB::INTEGERS])
            ->fetchAll(DB::OBJ);
        return $dimensions;
    }

    public static function loadAllForType(Connection $db, int $type)
    {
            return $db
                ->executeQuery('SELECT * FROM dimensions where type = ?', [$type], [DB::INTEGER])
                ->fetchAll(DB::OBJ);
    }

    public static function loadAllForLevel(Connection $db, $level)
    {
        $level = intval($level);
        $validLevels = [1,2,3];
        if (!in_array($level, $validLevels)) {
            return;
        }
        return $db
            ->executeQuery('SELECT *  FROM dimensions WHERE id in (select Level'.$level.' from dimensions_levels)')
            ->fetchAll(DB::OBJ);
    }

    public static function loadAllForLevelAndType(Connection $db, $level, $type)
    {
        $level = intval($level);
        $validLevels = [1,2,3];
        if (!in_array($level, $validLevels)) {
            return;
        }
        return $db
            ->executeQuery('SELECT *  FROM dimensions WHERE type = ? AND id in (select Level'.$level.' from dimensions_levels)', [$type], [DB::INTEGER])
            ->fetchAll(DB::OBJ);
    }

    public static function formatDimensionsAttribute(Connection $db, $value, $lookup)
    {
        if (empty($lookup) || empty($db)) {
            return $value;
        }

        if ($lookup->attributeType === LoAttributeTypes::DIMENSION) {
            $dimensions = self::loadAllForType($db, $lookup->dimensionId);

            $newVal = $value;
            if (isset($lookup->dimensionId)) {
                foreach ($dimensions as $dimension) {
                    if ($dimension->id == $newVal) {
                        $value = [
                            "key" => strval($newVal),
                            "value" => $dimension->name
                        ];
                    }
                }
            }
        }

        return $value;
    }
}
