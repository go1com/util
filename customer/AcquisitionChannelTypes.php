<?php

namespace go1\util\customer;

use ReflectionClass;

class AcquisitionChannelTypes
{
    const DIRECT             = 'direct';
    const SALES              = 'sales';
    const EXISTING_CUSTOMER  = 'existing_customer';
    const DISTRIBUTE_PARTNER = 'distribute_partner';
    const REFERRAL_PARTNER   = 'referral_partner';

    const S_DIRECT             = 'Direct';
    const S_SALES              = 'Sales';
    const S_EXISTING_CUSTOMER  = 'Existing Customer';
    const S_DISTRIBUTE_PARTNER = 'Distribute Partner';
    const S_REFERRAL_PARTNER   = 'Referral Partner';

    public static function all()
    {
        $rClass = new ReflectionClass(self::class);

        return $rClass->getConstants();
    }

    public static function getArray()
    {
        return [
            self::DIRECT             => self::S_DIRECT,
            self::SALES              => self::S_SALES,
            self::EXISTING_CUSTOMER  => self::S_EXISTING_CUSTOMER,
            self::DISTRIBUTE_PARTNER => self::S_DISTRIBUTE_PARTNER,
            self::REFERRAL_PARTNER   => self::S_REFERRAL_PARTNER,
        ];
    }
}
