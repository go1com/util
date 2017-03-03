<?php

namespace go1\util;

class GroupItemTypes
{
    const USER   = 'user';
    const NOTE   = 'note';
    const PORTAL = 'portal';
    const LO     = 'lo';
    const ALL    = [self::USER, self::NOTE, self::PORTAL, self::LO];
}
