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
    const CONTENT_LOADER = 'content_loader';
    const ALL = [
        self::FREE,
        self::SUBSCRIBE,
        self::PAID,
        self::CUSTOM,
        self::SHARE,
        self::CUSTOM_SHARE,
        self::CONTENT_LOADER
    ];
}
