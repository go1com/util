<?php

namespace go1\util\note;

class NoteStatus
{
    const ENTITY_STATUS_PRIVATE     = 0;
    const ENTITY_STATUS_ENROLLED    = 1;
    const ENTITY_STATUS_UNENROLLED  = 2;

    const TYPE_LO                   = 'lo';
    const TYPE_GROUP                = 'group';

    public static $loStatus = [self::ENTITY_STATUS_PRIVATE, self::ENTITY_STATUS_ENROLLED, self::ENTITY_STATUS_UNENROLLED];
}
