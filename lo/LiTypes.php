<?php

namespace go1\util\lo;

use ReflectionClass;

class LiTypes
{
    const ACTIVITY    = 'activities';
    const ATTENDANCE  = 'attendance';
    const ASSIGNMENT  = 'assignment';
    const DOCUMENT    = 'document';
    const H5P         = 'h5p';
    const IFRAME      = 'iframe';
    const INTERACTIVE = 'interactive';
    const QUESTION    = 'question';
    const QUIZ        = 'quiz';
    const RESOURCE    = 'resource';
    const TEXT        = 'text';
    const VIDEO       = 'video';
    const WORKSHOP    = 'workshop';
    const LTI         = 'lti';
    const COMPLEX     = ['assignment', 'h5p', 'interactive', 'quiz', 'lti'];

    public static function all()
    {
        $rSelf = new ReflectionClass(__CLASS__);

        $values = [];
        foreach ($rSelf->getConstants() as $const) {
            if (is_scalar($const)) {
                $values[] = $const;
            }
        }

        return $values;
    }
}
