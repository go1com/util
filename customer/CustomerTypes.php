<?php

namespace go1\util\customer;

use ReflectionClass;

class CustomerTypes
{
    const LEARNER_CUSTOMER  = 'learner_customer';
    const BUSINESS_CUSTOMER = 'business_customer';
    const TRAINING_ORG      = 'training_org';
    const PARTNER_CUSTOMER  = 'partner_customer';
    const PARTNER           = 'partner';

    const S_LEARNER_CUSTOMER  = 'Learner Customer';
    const S_BUSINESS_CUSTOMER = 'Business Customer';
    const S_TRAINING_ORG      = 'Training Org';
    const S_PARTNER_CUSTOMER  = 'Partner Customer';
    const S_PARTNER           = 'Partner';

    public static function all()
    {
        $rClass = new ReflectionClass(self::class);

        return $rClass->getConstants();
    }

    public static function getArray()
    {
        return [
            self::LEARNER_CUSTOMER  => self::S_LEARNER_CUSTOMER,
            self::BUSINESS_CUSTOMER => self::S_BUSINESS_CUSTOMER,
            self::TRAINING_ORG      => self::S_TRAINING_ORG,
            self::PARTNER_CUSTOMER  => self::S_PARTNER_CUSTOMER,
            self::PARTNER           => self::S_PARTNER,
        ];
    }
}
