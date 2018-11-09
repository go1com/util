<?php

namespace go1\util\award;

class AwardStatuses
{
    const PENDING     = -2;
    const ARCHIVED    = -1;
    const UNPUBLISHED = 0;
    const PUBLISHED   = 1;

    public static function all()
    {
        return [self::PENDING, self::ARCHIVED, self::UNPUBLISHED, self::PUBLISHED];
    }
}
