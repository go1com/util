<?php
namespace go1\util\collection;

class PortalCollectionConfiguration
{
    const FREE      = 'free';
    const PAID      = 'paid';
    const SUBSCRIBE = 'subscribe';
    const CUSTOM    = 'custom';
    const ALL       = [self::FREE, self::SUBSCRIBE, self::PAID, self::CUSTOM];
}
