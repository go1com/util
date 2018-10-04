<?php

namespace go1\util\lo\event_publishing;

class Events
{
    const EVENT_ATTENDANCE_CREATE = EventAttendanceCreate::ROUTING_KEY;
    const EVENT_ATTENDANCE_UPDATE = EventAttendanceUpdate::ROUTING_KEY;
    const EVENT_ATTENDANCE_DELETE = EventAttendanceDelete::ROUTING_KEY;
}
