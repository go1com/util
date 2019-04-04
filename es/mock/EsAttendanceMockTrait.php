<?php

namespace go1\util\es\mock;

use Elasticsearch\Client;
use go1\util\DateTime;
use go1\util\es\Schema;

trait EsAttendanceMockTrait
{
    public function createEsAttendance(Client $client, $options = [])
    {
        static $autoId;

        $event = $options['event'] ?? null;
        if (is_array($event) && $event) {
            $event = [
                'lo_id'                   => $event['lo_id'] ?? 1,
                'title'                   => $event['title'] ?? 'Event title',
                'start'                   => $event['start'] ?? DateTime::formatDate(time()),
                'end'                     => $event['end'] ?? DateTime::formatDate(time()),
                'timezone'                => $event['timezone'] ?? 'UTC',
                'seats'                   => $event['seats'] ?? 10,
                'available_seats'         => $event['available_seats'] ?? 10,
                'country'                 => $event['country'] ?? 'AU',
                'administrative_area'     => $event['administrative_area'] ?? '',
                'sub_administrative_area' => $event['sub_administrative_area'] ?? '',
                'locality'                => $event['locality'] ?? '',
                'dependent_locality'      => $event['dependent_locality'] ?? '',
                'thoroughfare'            => $event['thoroughfare'] ?? '',
                'premise'                 => $event['premise'] ?? '',
                'sub_premise'             => $event['sub_premise'] ?? '',
                'organisation_name'       => $event['organisation_name'] ?? '',
                'name_line'               => $event['name_line'] ?? '',
                'postal_code'             => $event['postal_code'] ?? '',
                'parent'                  => $event['parent'] ?? null,
                'coordinate'              => $event['coordinate'] ?? '',
                'location_name'           => $event['location_name'] ?? '',
                'module_title'            => $event['module_title'] ?? '',
                'instructor_ids'          => $event['instructor_ids'] ?? [],
                'instructors'             => $event['instructors'] ?? [],
                'metadata'                => [
                    'instance_id' => $event['instance_id'] ?? 0,
                    'updated_at'  => $event['updated_at'] ?? time(),
                ],
            ];
        }

        $attendance = [
            'id'           => $options['id'] ?? ++$autoId,
            'user_id'      => $options['user_id'] ?? 1,
            'lo_id'        => $options['lo_id'] ?? 1,
            'enrolment_id' => $options['enrolment_id'] ?? 1,
            'event_id'     => $options['event_id'] ?? 1,
            'portal_id'    => $options['portal_id'] ?? 1,
            'profile_id'   => $options['profile_id'] ?? 1,
            'start_at'     => $options['start_at'] ?? 1,
            'end_at'       => $options['end_at'] ?? 1,
            'status'       => $options['status'] ?? 1,
            'result'       => $options['result'] ?? 1,
            'pass'         => $options['pass'] ?? 0,
            'timestamp'    => $options['timestamp'] ?? 1,
            'event'        => $event,
            'metadata'     => [
                'instance_id' => intval($options['routing'] ?? $options['instance_id'] ?? 0),
            ],
        ];

        return $client->create([
            'index'   => $options['index'] ?? Schema::INDEX,
            'routing' => $options['routing'] ?? Schema::INDEX,
            'type'    => Schema::O_EVENT_ATTENDANCE,
            'id'      => $attendance['id'],
            'body'    => $attendance,
            'refresh' => true,
        ]);
    }
}
