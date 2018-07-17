<?php

namespace go1\util\portal;

use InvalidArgumentException;

class ChannelType
{
    const INTERNAL             = 'internal';
    const REFERRAL_PARTNER     = 'referral_partner';
    const DISTRIBUTION_PARTNER = 'distribution_partner';
    const SALES                = 'sales';
    const EXISTING_CUSTOMER    = 'existing_customer';
    const DIRECT               = 'direct';
    const PLATFORM_PARTNER     = 'platform_partner';

    public static function all()
    {
        return [self::INTERNAL, self::REFERRAL_PARTNER, self::DISTRIBUTION_PARTNER, self::SALES, self::EXISTING_CUSTOMER, self::DIRECT, self::PLATFORM_PARTNER];
    }

    public static function toString(string $type): string
    {
        switch ($type) {
            case self::INTERNAL:
                return 'Internal';

            case self::REFERRAL_PARTNER:
                return 'Referral Partner';

            case self::DISTRIBUTION_PARTNER:
                return 'Distribution Partner';

            case self::SALES:
                return 'SDR / Account Exec';

            case self::EXISTING_CUSTOMER:
                return 'Existing Customer';

            case self::DIRECT:
                return 'Direct or Inbound';

            case self::PLATFORM_PARTNER:
                return 'Platform Partner';

            default:
                throw new InvalidArgumentException('Unknown channel type: ' . $type);
        }
    }
}
