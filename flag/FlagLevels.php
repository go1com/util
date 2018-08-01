<?php

namespace go1\util\flag;

use ReflectionClass;

class FlagLevels
{
    const LEVEL_NONE     = 0;
    const LEVEL_TRIVIAL  = 1;
    const LEVEL_LOW      = 2;
    const LEVEL_MEDIUM   = 3;
    const LEVEL_HIGH     = 4;
    const LEVEL_CRITICAL = 5;


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

