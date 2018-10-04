<?php

namespace go1\util\lo\event_publishing;

class EventAttendanceUpdate extends EventAttendanceCreate
{
    const ROUTING_KEY = 'event.attendance.update';
}
