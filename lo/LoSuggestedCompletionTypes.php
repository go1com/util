<?php

namespace go1\util\lo;

class LoSuggestedCompletionTypes
{
    const DUE_DATE          = 1;
    const E_DURATION        = 2;
    const E_PARENT_DURATION = 3;
    const COURSE_ENROLMENT  = 4;

    const ALL = [self::DUE_DATE, self::E_DURATION, self::E_PARENT_DURATION, self::COURSE_ENROLMENT];
}
