<?php

namespace go1\util\schema\mock;

use Doctrine\DBAL\Connection;
use go1\util\DateTime;

trait AttendanceMockTrait
{
    public function createAttendance(Connection $db, array $options = []): int
    {
        $options['data'] = isset($options['data'])
            ? (is_scalar($options['data']) ? json_decode($options['data'], true) : $options['data'])
            : [];

        $db->insert('event_enrolment', [
            'id'             => $options['id'] ?? null,
            'status'         => $options['status'] ?? 'attending',
            'user_id'        => $options['user_id'] ?? 1,
            'lo_id'          => $options['lo_id'] ?? 1,
            'event_id'       => $options['event_id'] ?? 1,
            'enrolment_id'   => $options['enrolment_id'] ?? 1,
            'revision_id'    => $options['revision_id'] ?? 1,
            'portal_id'      => $options['portal_id'] ?? 1,
            'profile_id'     => $options['profile_id'] ?? 1,
            'taken_portal_id'=> $options['taken_portal_id'] ?? 1,
            'start_at'       => DateTime::formatDate($options['start_at'] ?? time()),
            'end_at'         => DateTime::formatDate($options['end_at'] ?? time()),
            'pass'           => $options['pass'] ?? 0,
            'changed_at'     => DateTime::formatDate($options['changed_at'] ?? time()),
            'timestamp'      => $options['timestamp'] ?? time(),
            'data'           => json_encode($options['data']),
            'published'      => $options['published'] ?? 1,
            'created_time'   => $options['created_time'] ?? time(),
            'updated_time'   => $options['updated_time'] ?? time(),
        ]);

        return $db->lastInsertId('event_enrolment');
    }
}
