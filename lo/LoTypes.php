<?php

namespace go1\util\lo;

class LoTypes
{
    const LEANING_PATHWAY = 'learning_pathway';
    const COURSE          = 'course';
    const MODULE          = 'module';
    const AWARD           = 'award';
    const GROUP           = 'group';
    const ACHIEVEMENT     = 'achievement';

    public static function all()
    {
        return [self::LEANING_PATHWAY, self::COURSE, self::MODULE, self::AWARD, self::GROUP, self::ACHIEVEMENT];
    }

    public static function allTheThing()
    {
        return array_merge(self::all(), LiTypes::all());
    }
}
