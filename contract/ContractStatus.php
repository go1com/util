<?php

namespace go1\util\contract;

class ContractStatus
{
    const STATUS_TRAILING = 'trialing';
    const STATUS_ACTIVE = 'active';
    const STATUS_CANCELED = 'canceled';

    public static function all()
    {
        return [
            self::STATUS_TRAILING => ucfirst(self::STATUS_TRAILING),
            self::STATUS_ACTIVE => ucfirst(self::STATUS_ACTIVE),
            self::STATUS_CANCELED => ucfirst(self::STATUS_CANCELED)
        ];
    }
}
