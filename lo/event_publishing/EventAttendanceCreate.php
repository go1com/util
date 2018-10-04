<?php

namespace go1\util\lo\event_publishing;

use stdClass;

class EventAttendanceCreate
{
    const ROUTING_KEY = 'event.attendance.create';

    public function publish(stdClass $body): stdClass
    {
        $attendance = new stdClass();
        $attendance->id              = $body->id ?? 0;
        $attendance->user_id         = $body->userId;
        $attendance->lo_id           = $body->loId;
        $attendance->enrolment_id    = $body->enrolmentId;
        $attendance->event_id        = $body->eventId;
        $attendance->revision_id     = $body->revisionId;
        $attendance->portal_id       = $body->portalId;
        $attendance->profileId       = $body->profileId;
        $attendance->taken_portal_id = $body->takenPortalId;
        $attendance->start_at        = $body->startAt;
        $attendance->end_at          = $body->endAt;
        $attendance->status          = $body->status;
        $attendance->result          = $body->result ?? null;
        $attendance->pass            = $body->pass;
        $attendance->changed_at      = $body->changedAt;
        $attendance->timestamp       = $body->timestamp;
        $attendance->data            = $body->data ?? null;
        $attendance->published       = $body->published;
        $attendance->created_time    = $body->createdTime;
        $attendance->updated_time    = $body->updatedTime;

        return $attendance;
    }
}
