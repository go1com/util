<?php

namespace go1\util\index;

class EnrolmentTypes
{
    const TYPE_ENROLMENT                    = 'enrolment';
    const TYPE_MANUAL_RECORD                = 'manual-record';
    const TYPE_PLAN_ASSIGNED                = 'plan-assigned';

    const ALL       = [self::TYPE_ENROLMENT, self::TYPE_MANUAL_RECORD, self::TYPE_PLAN_ASSIGNED];
}
