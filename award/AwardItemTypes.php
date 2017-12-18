<?php

namespace go1\util\award;

class AwardItemTypes
{
    const LO    = 'lo';
    const LI    = 'li';
    const AWARD = 'award';

    public static function all()
    {
        return [self::LO, self::LI, self::AWARD];
    }
}
