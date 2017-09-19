<?php

namespace go1\util\group;

use stdClass;

class GroupTypes
{
    const DEFAULT       = 'default';
    const MARKETPLACE   = 'marketplace';
    const PREMIUM       = 'premium';
    const VIRTUAL       = 'virtual';

    const ALL = [self::DEFAULT, self::MARKETPLACE, self::PREMIUM, self::VIRTUAL];

    public static function isDefault(stdClass $group)
    {
        return $group->type == self::DEFAULT;
    }

    public static function isPremium(stdClass $group)
    {
        return $group->type == self::PREMIUM;
    }

    public static function isMarketplace(stdClass $group)
    {
        return $group->type == self::MARKETPLACE;
    }

    public static function isVirtual(stdClass $group)
    {
        return $group->type == self::VIRTUAL;
    }
}
