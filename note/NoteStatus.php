<?php

namespace go1\util\note;

class NoteStatus
{
    const LO_STATUS_PRIVATE = 0;
    const LO_STATUS_COURSE  = 1;
    const LO_STATUS_PUBLIC  = 2;

    const TYPE_LO       = 'lo';
    const TYPE_GROUP    = 'group';

    public static $loStatus = [self::LO_STATUS_PRIVATE, self::LO_STATUS_COURSE, self::LO_STATUS_PUBLIC];
}
