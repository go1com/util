<?php

namespace go1\util\lo;

class LoAttributes
{
    const MOBILE_OPTIMISED = 1;
    const WCAG             = 2;  // Web Content Accessibility Guidelines compatible
    const ASSESSABLE       = 3;
    const AVAILABILITY     = 4;  // marketplace

    public static function machineName(int $attribute): ?string
    {
        $map = [
            self::MOBILE_OPTIMISED => 'mobile_optimised',
            self::WCAG             => 'wcag',
            self::ASSESSABLE       => 'assessable',
            self::AVAILABILITY     => 'availability',
        ];

        return $map[$attribute] ?? null;
    }
}
