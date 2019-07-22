<?php

namespace go1\util\event;

class AttendanceStatuses
{
    const ATTENDED     = 'attended';
    const NOT_ATTENDED = 'not-attended';
    const ATTENDING    = 'attending';
    const PENDING      = 'pending';

    const I_PENDING           = 0;
    const I_ATTENDING         = 1;
    const I_ATTENDED          = 2;
    const I_NOT_ATTENDED      = 3;
}
