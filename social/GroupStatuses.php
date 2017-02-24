<?php

namespace go1\util\social;

class GroupStatuses
{
    const PUBLIC    = 'public';  # All portal users can join
    const PRIVATE   = 'private'; # Only group owner
    const SECRET    = 'secret';  # Group owner can invite other portal users.

    /**
     * All available values that user can input.
     */
    public static function all()
    {
        return [self::PUBLIC, self::PRIVATE, self::SECRET];
    }
}
