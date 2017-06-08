<?php

namespace go1\util\payment;

class PaymentMethods
{
    const COD             = 'cod';
    const STRIPE          = 'stripe';
    const CREDIT          = 'credit';

    public static function all()
    {
        return [self::COD, self::STRIPE, self::CREDIT];
    }
}
