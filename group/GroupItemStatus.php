<?php

namespace go1\util\group;

class GroupItemStatus
{
    const BLOCKED  = -3;
    const REJECTED = -2;
    const PENDING  = -1;
    const ACTIVE   = 1;
    const ALL      = [self::BLOCKED, self::REJECTED, self::PENDING, self::ACTIVE];

    const PUBLISHED     = 1;
    const UNPUBLISHED   = 0;
}
