<?php

namespace go1\util\flag;

use ReflectionClass;

class FlagReason
{
    const REASON_LOADING       = 1;
    const REASON_GRAMMAR       = 2;
    const REASON_INACCURACY    = 3;
    const REASON_INAPPROPRIATE = 4;


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

