<?php

namespace go1\util\customer;

use ReflectionClass;

class IndustryTypes
{
    const GOVERNMENT              = 'government';
    const EDUCATION               = 'education';
    const HEALTH_SUPPORT_SERVICES = 'health_support_services';
    const ENGINEERING             = 'engineering';
    const BUILDING_SUPPLIES       = 'building_supplies';

    const S_GOVERNMENT              = 'Government';
    const S_EDUCATION               = 'Education';
    const S_HEALTH_SUPPORT_SERVICES = 'Health Support Services';
    const S_ENGINEERING             = 'Engineering';
    const S_BUILDING_SUPPLIES       = 'Building Supplies';

    public static function all()
    {
        $rClass = new ReflectionClass(self::class);

        return $rClass->getConstants();
    }

    public static function getArray()
    {
        return [
            self::GOVERNMENT              => self::S_GOVERNMENT,
            self::EDUCATION               => self::S_EDUCATION,
            self::HEALTH_SUPPORT_SERVICES => self::S_HEALTH_SUPPORT_SERVICES,
            self::ENGINEERING             => self::S_ENGINEERING,
            self::BUILDING_SUPPLIES       => self::S_BUILDING_SUPPLIES,
        ];
    }
}
