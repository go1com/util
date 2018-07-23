<?php

namespace go1\util\collection;

class PortalCollectionConfiguration
{
    const FREE         = 'free';
    const PAID         = 'paid';
    const SUBSCRIBE    = 'subscribe';
    const CUSTOM       = 'custom';
    const SHARE        = 'share';
    const CUSTOM_SHARE = 'custom_share';
    const ALL          = [self::FREE, self::SUBSCRIBE, self::PAID, self::CUSTOM, self::SHARE, self::CUSTOM_SHARE];
}
