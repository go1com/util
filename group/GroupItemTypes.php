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
            case GroupTypes::CONTENT:
                return [self::LO];

            case GroupTypes::CONTENT_PACKAGE:
                return [self::GROUP, self::PORTAL];
        }

        return [self::USER, self::NOTE, self::LO];
    }
}
