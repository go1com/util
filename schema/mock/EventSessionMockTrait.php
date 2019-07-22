<?php

namespace go1\util\schema\mock;

use Doctrine\DBAL\Connection;
use go1\util\DateTime;

trait EventSessionMockTrait
{
    public function createEventSession(Connection $db, array $options = []): int
    {
        $options['data'] = isset($options['data'])
            ? (is_scalar($options['data']) ? json_decode($options['data'], true) : $options['data'])
            : [];

        $db->insert('event_session', [
            'id'             => $options['id'] ?? null,
            'title'          => $options['title'] ?? 'GO1 Event Session',
            'portal_id'      => $options['portal_id'] ?? 1,
            'lo_id'          => $options['lo_id'] ?? 1,
            'location_id'    => $options['location_id'] ?? 1,
            'start_at'       => DateTime::formatDate($options['start_at'] ?? time()),
            'end_at'         => DateTime::formatDate($options['end_at'] ?? time()),
            'timezone'       => $options['timezone'] ?? 'UTC',
            'url'            => $options['url'] ?? null,
            'instructor_ids' => serialize($options['instructor_ids'] ?? []),
            'description'    => $options['description'] ?? null,
            'attendee_limit' => $options['attendee_limit'] ?? null,
            'data'           => json_encode($options['data']),
            'published'      => $options['published'] ?? 1,
            'created_time'   => $options['created_time'] ?? time(),
            'updated_time'   => $options['updated_time'] ?? time(),
        ]);

        return $db->lastInsertId('event_session');
    }
}
