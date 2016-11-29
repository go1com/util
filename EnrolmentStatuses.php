<?php

namespace go1\util;

class EnrolmentStatuses
{
    const ASSIGNED    = 'assigned';
    const IN_PROGRESS = 'in-progress';
    const PENDING     = 'pending';
    const COMPLETED   = 'completed';
    const EXPIRED     = 'expired';

    public static function all()
    {
        return [self::ASSIGNED, self::IN_PROGRESS, self::PENDING, self::COMPLETED];
    }
}
