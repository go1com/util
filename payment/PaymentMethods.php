<?php

namespace go1\util\payment;

class PaymentMethods
{
    const COD             = 'cod';
    const STRIPE          = 'stripe';
    const CREDIT          = 'credit';
    const MANUAL          = 'manual';

    public static function all()
    {
        return [self::COD, self::STRIPE, self::CREDIT, self::MANUAL];
    }

    public static function skipValidateOption(string $method) : bool
    {
        return in_array($method, [self::COD, self::MANUAL]);
    }
}
