<?php

namespace go1\util\portal;

class ChannelType extends ConstantContainer
{
    static protected $name = 'channel';

    static protected $customFormats = [
        self::SALES => 'SDR / Account Exec',
        self::DIRECT => 'Direct or Inbound',
    ];

    const INTERNAL             = 'internal';
    const REFERRAL_PARTNER     = 'referral_partner';
    const DISTRIBUTION_PARTNER = 'distribution_partner';
    const SALES                = 'sales';
    const EXISTING_CUSTOMER    = 'existing_customer';
    const DIRECT               = 'direct';
    const PLATFORM_PARTNER     = 'platform_partner';
    const PORTAL_LAUNCHER      = 'portal_launcher';
    const CONTENT_PARTNER      = 'content_partner';
}
