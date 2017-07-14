<?php

namespace go1\util\award;

use ReflectionClass;

class AwardTypes
{
    const MANUAL_TYPE_BOOK    = 'book';
    const MANUAL_TYPE_ARTICLE = 'article';
    const MANUAL_TYPE_JOURNAL = 'journal';
    const MANUAL_TYPE_F2F     = 'face to face';
    const MANUAL_TYPE_ONLINE  = 'online';
    const MANUAL_TYPE_OTHER   = 'other';

    public static function allManualTypes()
    {
        $rClass = new ReflectionClass(static::class);

        return $rClass->getConstants();
    }
}
