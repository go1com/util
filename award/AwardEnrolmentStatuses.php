<?php

namespace go1\util\award;

class AwardEnrolmentStatuses
{
    const IN_PROGRESS = 1;
    const COMPLETED   = 2;
    const EXPIRED     = 3;

    public static function all()
    {
        return [
            static::IN_PROGRESS,
            static::COMPLETED,
            static::EXPIRED,
        ];
    }
}
