<?php

namespace go1\util\contract;

class ContractInterval
{
    const INTERVAL_MONTH = 'month';
    const INTERVAL_YEAR = 'year';

    public static function all()
    {
        return [
            self::INTERVAL_MONTH => ucfirst(self::INTERVAL_MONTH),
            self::INTERVAL_YEAR => ucfirst(self::INTERVAL_YEAR)
        ];
    }
}
