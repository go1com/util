<?php

namespace go1\util\group;

class GroupItemTypes
{
    const USER   = 'user';
    const NOTE   = 'note';
    const PORTAL = 'portal';
    const LO     = 'lo';
    const GROUP  = 'group';
    const AWARD  = 'award';

    const ALL = [self::USER, self::NOTE, self::PORTAL, self::LO, self::GROUP, self::AWARD];

    public static function items(string $type): array
    {
        switch ($type) {
            case 'premium':
                $types = [self::LO];
                break;

            case 'marketplace':
                $types = [self::GROUP, self::PORTAL];
                break;

            case '_none';
            default:
                $types = [self::USER, self::NOTE, self::LO];
                break;
        }

        return $types ?? [];
    }
}
