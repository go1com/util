<?php

namespace go1\util\customer;

use ReflectionClass;

class RegionTypes
{
    const ANZ             = 'anz';
    const SOUTH_EAST_ASIA = 'south_east_asia';
    const AMERICAS        = 'americas';
    const EUROPE          = 'europe';
    const REST_OF_WORLD   = 'rest_of_world';

    const S_ANZ             = 'ANZ';
    const S_SOUTH_EAST_ASIA = 'South East Asia';
    const S_AMERICAS        = 'Americas';
    const S_EUROPE          = 'Europe';
    const S_REST_OF_WORLD   = 'Rest of World';

    public static function all()
    {
        $rClass = new ReflectionClass(self::class);

        return $rClass->getConstants();
    }

    public static function getArray()
    {
        return [
            self::ANZ             => self::S_ANZ,
            self::SOUTH_EAST_ASIA => self::S_SOUTH_EAST_ASIA,
            self::AMERICAS        => self::S_AMERICAS,
            self::EUROPE          => self::S_EUROPE,
            self::REST_OF_WORLD   => self::S_REST_OF_WORLD,
        ];
    }
}
