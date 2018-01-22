<?php

namespace go1\util\metric;

use InvalidArgumentException;

class MetricTypes
{
    const NEW_ARR     = 1;
    const TOTAL_ARR   = 2;
    const NET_CHURN   = 3;
    const S_NEW_ARR   = 'New ARR';
    const S_TOTAL_ARR = 'Total ARR';
    const S_NET_CHURN = 'Net Churn';

    public static function all()
    {
        return [
            self::NEW_ARR,
            self::TOTAL_ARR,
            self::NET_CHURN,
        ];
    }

    public static function toNumeric(string $type): int
    {
        switch ($type) {
            case self::S_NEW_ARR:
                return self::NEW_ARR;

            case self::S_TOTAL_ARR:
                return self::TOTAL_ARR;

            case self::S_NET_CHURN:
                return self::NET_CHURN;

            default:
                throw new InvalidArgumentException('Unknown metric type: ' . $type);
        }
    }

    public static function toString(int $type): string
    {
        switch ($type) {
            case self::NEW_ARR:
                return self::S_NEW_ARR;

            case self::TOTAL_ARR:
                return self::S_TOTAL_ARR;

            case self::NET_CHURN:
                return self::S_NET_CHURN;

            default:
                throw new InvalidArgumentException('Unknown metric type: ' . $type);
        }
    }
}
