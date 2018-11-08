<?php

namespace go1\util\event;

class AttendanceStatuses
{
    const ATTENDED     = 'attended';
    const NOT_ATTENDED = 'not-attended';
    const ATTENDING    = 'attending';
    const PENDING      = 'pending';

    const NUM_PENDING           = 0;
    const NUM_ATTENDING         = 1;
    const NUM_ATTENDED          = 2;
    const NUM_NOT_ATTENDED      = 3;
}
