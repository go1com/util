<?php

namespace go1\util\customer;

use InvalidArgumentException;

class RegionType
{
    const ANZ             = 'anz';
    const SOUTH_EAST_ASIA = 'south_east_asia';
    const AMERICAS        = 'americas';
    const EUROPE          = 'europe';
    const REST_OF_WORLD   = 'rest_of_world';

    public static function all()
    {
        $rClass = new ReflectionClass(self::class);

        return $rClass->getConstants();
    }

    public static function getArray()
    {
        return [
            self::ANZ             => self::toString(self::ANZ),
            self::SOUTH_EAST_ASIA => self::toString(self::SOUTH_EAST_ASIA),
            self::AMERICAS        => self::toString(self::AMERICAS),
            self::EUROPE          => self::toString(self::EUROPE),
            self::REST_OF_WORLD   => self::toString(self::REST_OF_WORLD),
        ];
    }

    public static function toString(string $type): string
    {
        switch ($type) {
            case self::ANZ:
                return 'ANZ';

            case self::SOUTH_EAST_ASIA:
                return 'South East Asia';

            case self::AMERICAS:
                return 'Americas';

            case self::EUROPE:
                return 'Europe';

            case self::REST_OF_WORLD:
                return 'Rest of World';

            default:
                throw new InvalidArgumentException('Unknown Region type: ' . $type);
        }
    }
}
