<?php

namespace go1\util;

class LoTypes
{
    const LEANING_PATHWAY = 'learning_pathway';
    const COURSE          = 'course';
    const MODULE          = 'module';

    public static function all()
    {
        return ['learning_pathway', 'course', 'module'];
    }
}
