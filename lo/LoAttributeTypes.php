<?php

namespace go1\util\lo;

use ReflectionClass;

class LoAttributeTypes
{

    const BOOLEAN   = "BOOLEAN";
    const INTEGER   = "INTEGER";
    const TEXT      = "TEXT";
    const DIMENSION = "DIMENSION";

    public static function all()
    {
        $rSelf = new ReflectionClass(__CLASS__);

        $values = [];
        foreach ($rSelf->getConstants() as $const) {
            if (is_scalar($const)) {
                $values[] = $const;
            }
        }

        return $values;
    }
}
