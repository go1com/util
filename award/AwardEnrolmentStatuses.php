<?php

namespace go1\util\award;

use go1\util\enrolment\EnrolmentStatuses;
use go1\util\plan\PlanStatuses;
use InvalidArgumentException;

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

    public static function toString(int $status): string
    {
        switch ($status) {
            case self::IN_PROGRESS:
                return self::S_IN_PROGRESS;

            case self::COMPLETED:
                return self::S_COMPLETED;

            case self::EXPIRED:
                return self::S_EXPIRED;

            default:
                throw new InvalidArgumentException('Unknown enrolment status: ' . $status);
        }
    }

    public static function toEsNumeric(int $status): int
    {
        switch ($status) {
            case self::IN_PROGRESS:
                return EnrolmentStatuses::I_IN_PROGRESS;

            case self::COMPLETED:
                return EnrolmentStatuses::I_COMPLETED;

            case self::EXPIRED:
                return EnrolmentStatuses::I_EXPIRED;

            case PlanStatuses::ASSIGNED:
                return PlanStatuses::ASSIGNED;

            default:
                throw new InvalidArgumentException('Unknown enrolment status: ' . $status);
        }
    }
}
