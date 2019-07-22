<?php

namespace go1\util\portal;

class PortalType extends ConstantContainer
{
    static protected $name = 'portal';

    static protected $customFormats = [
        self::JSE_CUSTOMER => 'JSE Customer'
    ];

    const CONTENT_PARTNER      = 'content_partner';
    const DISTRIBUTION_PARTNER = 'distribution_partner';
    const INTERNAL             = 'internal';
    const CUSTOMER             = 'customer';
    const COMPLISPACE          = 'complispace';
    const JSE_CUSTOMER         = 'jse_customer';
    const TOTARA_CUSTOMER      = 'totara_customer';
}
