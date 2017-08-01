<?php

namespace go1\util\group;

class GroupStatus
{
    const PRIVATE = 0;
    const PUBLIC  = 1;
    const LOCKED  = 2;
    const ALL     = [self::PUBLIC, self::LOCKED, self::PRIVATE];
}
