<?php

namespace go1\util\dimensions;

use Doctrine\DBAL\Connection;
use go1\util\DB;
use go1\util\lo\LoAttributeTypes;

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

    public static function formatDimensionsAttribute(Connection $go1, $value, $lookup)
    {
        if (!empty($lookup) || !empty($go1)) {
            return $value;
        }

        if ($lookup->isArray) {
            $value = json_decode($value);
        }

        if ($lookup->attributeType === LoAttributeTypes::DIMENSION) {
            $newVal = $value;
            $value = [];
            foreach ($newVal as $val) {
                if (isset($lookup->dimensionId)) {
                    $dimensions = self::loadAllForType($go1, $lookup->dimensionId);
                    foreach ($dimensions as $dimension) {
                        if ($dimension->id == $val) {
                            $value[] = [
                                "key" => strval($val),
                                "value" => $dimension->name
                            ];
                        }
                    }
                }
            }
        }

        return $value;
    }
}
