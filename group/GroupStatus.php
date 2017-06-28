<?php

namespace go1\util\group;

class GroupStatus
{
    const PUBLIC  = 1;
    const LOCKED  = 0;
    const PRIVATE = 2;
    const ALL     = [self::PUBLIC, self::LOCKED, self::PRIVATE];
}
