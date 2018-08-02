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
    const JSE_CUSTOMER         = 'jse_customer';
    const TOTARA_CUSTOMER      = 'totara_customer';

    public static function all()
    {
        return [
            self::CONTENT_PARTNER,
            self::DISTRIBUTION_PARTNER,
            self::INTERNAL,
            self::CUSTOMER,
            self::COMPLISPACE,
            self::JSE_CUSTOMER,
            self::TOTARA_CUSTOMER,
        ];
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

            case self::JSE_CUSTOMER:
                return 'JSE Customer';

            case self::TOTARA_CUSTOMER:
                return 'Totara Customer';

            default:
                throw new InvalidArgumentException('Unknown portal type: ' . $type);
        }
    }
}
