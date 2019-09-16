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
    const MANUAL      = 'manual';
    /**
     * @deprecated use the LINK type instead
     */
    const IFRAME      = 'iframe';
    const LINK        = 'link';
    const INTERACTIVE = 'interactive';
    const QUESTION    = 'question';
    const QUIZ        = 'quiz';
    /**
     * @deprecated use the TEXT type instead
     */
    const RESOURCE    = 'resource';
    const TEXT        = 'text';
    const VIDEO       = 'video';
    const WORKSHOP    = 'workshop';
    const LTI         = 'lti';
    const EVENT       = 'event';
    const INTEGRATION = 'integration';
    const COMPLEX     = ['assignment', 'h5p', 'interactive', 'quiz', 'lti', 'event'];

    const PRIVATE_PROPERTIES = [
        self::DOCUMENT    => ['path'],
        self::H5P         => ['path'],
        self::INTERACTIVE => ['url'],
    ];

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
