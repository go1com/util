<?php

namespace go1\util\customer;

use InvalidArgumentException;

class IndustryType
{
    const LEANER_CUSTOMER   = 'leaner_customer';
    const BUSINESS_CUSTOMER = 'business_customer';
    const TRAINING_ORG      = 'training_org';
    const PARTNER_CUSTOMER  = 'partner_customer';
    const PARTNER           = 'partner';

    public static function all()
    {
        $rClass = new ReflectionClass(self::class);

        return $rClass->getConstants();
    }

    public static function getArray()
    {
        return [
            self::LEANER_CUSTOMER   => self::toString(self::LEANER_CUSTOMER),
            self::BUSINESS_CUSTOMER => self::toString(self::BUSINESS_CUSTOMER),
            self::TRAINING_ORG      => self::toString(self::TRAINING_ORG),
            self::PARTNER_CUSTOMER  => self::toString(self::PARTNER_CUSTOMER),
            self::PARTNER           => self::toString(self::PARTNER),
        ];
    }

    public static function toString(string $type): string
    {
        switch ($type) {
            case self::LEANER_CUSTOMER:
                return 'Leaner customer';

            case self::BUSINESS_CUSTOMER:
                return 'Business customer';

            case self::TRAINING_ORG:
                return 'Training org';

            case self::PARTNER_CUSTOMER:
                return 'Partner customer';

            case self::PARTNER:
                return 'Partner';

            default:
                throw new InvalidArgumentException('Unknown industry type: ' . $type);
        }
    }
}
