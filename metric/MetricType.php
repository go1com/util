<?php
namespace go1\util\metric;

class MetricType
{
    const NEW_ARR      = 1;
    const TOTAL_ARR    = 2;
    const NET_CHURN    = 3;
    const TOTAL_PORTAL = 4;

    public static function all()
    {
        return [
            self::NEW_ARR,
            self::TOTAL_ARR,
            self::NET_CHURN,
            self::TOTAL_PORTAL,
        ];
    }
}
