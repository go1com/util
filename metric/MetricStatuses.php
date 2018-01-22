<?php

namespace go1\util\metric;

use InvalidArgumentException;

class MetricStatuses
{
    const ACTIVE     = 1;
    const INACTIVE   = 0;
    const S_ACTIVE   = 'Active';
    const S_INACTIVE = 'Inactive';

    public static function all()
    {
        return [
            self::ACTIVE,
            self::INACTIVE,
        ];
    }

    public static function toNumeric(string $type): int
    {
        switch ($type) {
            case self::S_ACTIVE:
                return self::ACTIVE;

            case self::S_INACTIVE:
                return self::INACTIVE;

            default:
                throw new InvalidArgumentException('Unknown metric status: ' . $type);
        }
    }

    public static function toString(int $type): string
    {
        switch ($type) {
            case self::ACTIVE:
                return self::S_ACTIVE;

            case self::INACTIVE:
                return self::S_INACTIVE;

            default:
                throw new InvalidArgumentException('Unknown metric status: ' . $type);
        }
    }
}
