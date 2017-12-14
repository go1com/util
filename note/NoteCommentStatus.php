<?php

namespace go1\util\note;

class NoteCommentStatus
{
    const DISABLED = 0;
    const ENABLED  = 1;

    public static $statuses = [self::DISABLED, self::ENABLED];
}
