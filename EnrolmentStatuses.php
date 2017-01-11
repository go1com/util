<?php

namespace go1\util;

class EnrolmentStatuses
{
    const ASSIGNED    = 'assigned';    # Someone added this for you to do
    const IN_PROGRESS = 'in-progress'; # you are learning the LO.
    const PENDING     = 'pending';     # you have enrolled but not yet opened the course
    const COMPLETED   = 'completed';   # you get this state when you finish the course
    const EXPIRED     = 'expired';     # your enrolment was completed, but it's expired.

    /**
     * All available values that user can input.
     * Expired is only set by our background logic.
     */
    public static function all()
    {
        return [self::ASSIGNED, self::IN_PROGRESS, self::PENDING, self::COMPLETED];
    }
}
