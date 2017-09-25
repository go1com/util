<?php

namespace go1\util\award;

class AwardEnrolmentStatuses
{
    const IN_PROGRESS = 1;
    const COMPLETED   = 2;
    const EXPIRED     = 3;

    const S_IN_PROGRESS = 'in-progress';
    const S_COMPLETED   = 'completed';
    const S_EXPIRED     = 'expired';

    public static function all()
    {
        return [
            static::IN_PROGRESS,
            static::COMPLETED,
            static::EXPIRED,
        ];
    }
}
