<?php

namespace go1\util\portal;

use ReflectionClass;

class PartnerType
{
    public const COMPLISPACE = 'complispace';
    public const GO1 = 'go1';
    public const JOBREADY = 'jobready';
    public const JSE = 'jse';
    public const PARTNER_HUB = 'partnerhub';
    public const TOTARA = 'totara';
    public const XERO = 'xero';

    public static function all(): array
    {
        return array_values(
            (new ReflectionClass(__CLASS__))->getConstants()
        );
    }

    public static function toString(string $type): string
    {
        switch ($type) {
            case self::COMPLISPACE:
                return 'Complispace';

            case self::GO1:
                return 'GO1';

            case self::JOBREADY:
                return 'JobReady';

            case self::JSE:
                return 'JSE';

            case self::PARTNER_HUB:
                return 'Partner Hub';

            case self::TOTARA:
                return 'Totara';

            case self::XERO:
                return 'Xero';

            default:
                throw new InvalidArgumentException('Unknown partner type: ' . $type);
        }
    }
}
