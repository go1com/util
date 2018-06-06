<?php

namespace go1\util\portal;

use InvalidArgumentException;

class PortalState
{
    const TRIAL       = 'trial';
    const ONBOARDING  = 'onboarding';
    const LIVE        = 'live';
    const EXPIRED     = 'expired';
    const SUSPENDED   = 'suspended';
    const CANCELLED   = 'cancelled';
    const TEST        = 'test';
    const TEMPLATE    = 'template';
    const DEVELOPMENT = 'development';

    public static function all()
    {
        return [self::TRIAL, self::ONBOARDING, self::LIVE, self::EXPIRED, self::SUSPENDED, self::CANCELLED, self::TEST, self::TEMPLATE, self::DEVELOPMENT];
    }

    public static function toString(string $state): string
    {
        switch ($state) {
            case self::TRIAL:
                return 'Demo / Trial';

            case self::ONBOARDING:
                return 'Onboarding';

            case self::LIVE:
                return 'Live';

            case self::EXPIRED:
                return 'Expired Demo / Trial';

            case self::SUSPENDED:
                return 'Suspended';

            case self::CANCELLED:
                return 'Cancelled';

            case self::TEST:
                return 'Test';

            case self::TEMPLATE:
                return 'Sample & Template';

            case self::DEVELOPMENT:
                return 'Development';

            default:
                throw new InvalidArgumentException('Unknown portal stage: ' . $state);
        }
    }
}
