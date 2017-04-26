<?php

namespace go1\util\assignment;

class SubmissionStatuses
{
    const INVALID   = 0;
    const PENDING   = 1;
    const REVIEWING = 2;
    const REDO      = 3;
    const DONE      = 4;

    const ALL       = [self::PENDING, self::REVIEWING, self::REDO, self::DONE];
}
