<?php

namespace go1\util\portal;

class PortalType
{
    const CONTENT_PARTNER = 'content_partner';
    const DISTRIBUTION    = 'distribution_partner';
    const INTERNAL        = 'internal';
    const CUSTOMER        = 'customer';
    const COMPLISPACE     = 'complispace';

    public static function all()
    {
        return [self::CONTENT_PARTNER, self::DISTRIBUTION, self::INTERNAL, self::CUSTOMER, self::COMPLISPACE];
    }
}
