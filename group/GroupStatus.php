<?php

namespace go1\util\group;

class GroupStatus
{
    const PUBLIC  = 1;
    const PRIVATE = 0;
    const ALL     = [self::PUBLIC, self::PRIVATE];
}
