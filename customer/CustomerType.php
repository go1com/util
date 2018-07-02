<?php

namespace go1\util\customer;

use ReflectionClass;
use InvalidArgumentException;

class CustomerType
{
    const GOVERNMENT              = 'government';
    const EDUCATION               = 'education';
    const HEALTH_SUPPORT_SERVICES = 'health_support_services';
    const ENGINEERING             = 'engineering';
    const BUILDING_SUPPLIES       = 'building_supplies';

    public static function all()
    {
        $rClass = new ReflectionClass(self::class);

        return $rClass->getConstants();
    }

    public static function getArray()
    {
        return [
            self::GOVERNMENT              => self::toString(self::GOVERNMENT),
            self::EDUCATION               => self::toString(self::EDUCATION),
            self::HEALTH_SUPPORT_SERVICES => self::toString(self::HEALTH_SUPPORT_SERVICES),
            self::ENGINEERING             => self::toString(self::ENGINEERING),
            self::BUILDING_SUPPLIES       => self::toString(self::BUILDING_SUPPLIES),
        ];
    }

    public static function toString(string $type): string
    {
        switch ($type) {
            case self::GOVERNMENT:
                return 'Government';

            case self::EDUCATION:
                return 'Education';

            case self::HEALTH_SUPPORT_SERVICES:
                return 'Health Support Services';

            case self::ENGINEERING:
                return 'Engineering';

            case self::BUILDING_SUPPLIES:
                return 'Building Supplies';

            default:
                throw new InvalidArgumentException('Unknown customer type: ' . $type);
        }
    }
}
