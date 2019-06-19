<?php

namespace go1\util\dimensions;

class DimensionType
{
    const TOPIC               = 1;
    const INDUSTRY            = 2;
    const REGION_RESTRICTION  = 3;
    const LOCATION            = 4;
    const BUSINESS_AREA       = 5;

    public static function all()
    {
        $rSelf = new \ReflectionClass(__CLASS__);
        $values = [];
        foreach ($rSelf->getConstants() as $const) {
            if (is_scalar($const)) {
                $values[] = $const;
            }
        }

        return $values;
    }
}
