<?php

namespace go1\util;

use ReflectionClass;

class LiTypes
{
    const ACTIVITY    = 'activities';
    const ATTENDANCE  = 'attendance';
    const DOCUMENT    = 'document';
    const H5P         = 'h5p';
    const IFRAME      = 'iframe';
    const QUESTION    = 'question';
    const QUIZ        = 'quiz';
    const RESOURCE    = 'resource';
    const TEXT        = 'text';
    const INTERACTIVE = 'interactive';
    const VIDEO       = 'video';
    const WORKSHOP    = 'workshop';

    public static function all()
    {
        $rSelf = new ReflectionClass(__CLASS__);

        return array_values($rSelf->getConstants());
    }
}
