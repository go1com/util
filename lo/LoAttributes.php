<?php

namespace go1\util\lo;

use ReflectionClass;

class LoAttributes
{
    const MOBILE_OPTIMISED = 1;
    const WCAG             = 2;  // Web Content Accessibility Guidelines compatible
    const ASSESSABLE       = 3;
    const AVAILABILITY     = 4;  // marketplace
    const REGION_RESTRICTION = 5;

    public static function machineName(int $attribute): ?string
    {
        $map = [
            self::MOBILE_OPTIMISED => 'mobile_optimised',
            self::WCAG             => 'wcag',
            self::ASSESSABLE       => 'assessable',
            self::AVAILABILITY     => 'availability',
            self::REGION_RESTRICTION => 'region_restriction'
        ];

        return $map[$attribute] ?? null;
    }

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
