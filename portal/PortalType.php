<?php

namespace go1\util\portal;

use InvalidArgumentException;

class PortalType
{
    const CONTENT_PARTNER      = 'content_partner';
    const DISTRIBUTION_PARTNER = 'distribution_partner';
    const INTERNAL             = 'internal';
    const CUSTOMER             = 'customer';
    const COMPLISPACE          = 'complispace';

    public static function all()
    {
        return [self::CONTENT_PARTNER, self::DISTRIBUTION_PARTNER, self::INTERNAL, self::CUSTOMER, self::COMPLISPACE];
    }

    public static function toString(string $type): string
    {
        switch ($type) {
            case self::CONTENT_PARTNER:
                return 'Content Partner';

            case self::DISTRIBUTION_PARTNER:
                return 'Distribution Partner';

            case self::INTERNAL:
                return 'Internal';

            case self::CUSTOMER:
                return 'Customer';

            case self::COMPLISPACE:
                return 'Complispace';

            default:
                throw new InvalidArgumentException('Unknown portal type: ' . $type);
        }
    }
}
