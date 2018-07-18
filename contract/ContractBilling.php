<?php

namespace go1\util\contract;

class ContractBilling
{
    const BILLING_CHARGE = 'charge_automatically';
    const BILLING_INVOICE = 'send_invoice';

    public static function all()
    {
        return [
            self::BILLING_CHARGE => ucwords(str_replace('_', ' ', self::BILLING_CHARGE)),
            self::BILLING_INVOICE => ucwords(str_replace('_', ' ', self::BILLING_INVOICE))
        ];
    }
}
