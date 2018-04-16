<?php

namespace go1\util\es\mock;

use Elasticsearch\Client;
use go1\util\DateTime;
use go1\util\es\Schema;

trait EsEventMockTrait
{
    public function createEsEvent(Client $client, $options = [])
    {
        static $autoId;

        $event = [
            'lo_id'                   => $options['lo_id'] ?? ++$autoId,
            'start'                   => $options['start'] ?? DateTime::formatDate(time()),
            'end'                     => $options['end'] ?? DateTime::formatDate(time()),
            'timezone'                => $options['timezone'] ?? 'UTC',
            'seats'                   => $options['seats'] ?? 10,
            'available_seats'         => $options['available_seats'] ?? 10,
            'country'                 => $options['country'] ?? 'AU',
            'administrative_area'     => $options['administrative_area'] ?? '',
            'sub_administrative_area' => $options['sub_administrative_area'] ?? '',
            'locality'                => $options['locality'] ?? '',
            'dependent_locality'      => $options['dependent_locality'] ?? '',
            'thoroughfare'            => $options['thoroughfare'] ?? '',
            'premise'                 => $options['premise'] ?? '',
            'sub_premise'             => $options['sub_premise'] ?? '',
            'organisation_name'       => $options['organisation_name'] ?? '',
            'name_line'               => $options['name_line'] ?? '',
            'postal_code'             => $options['postal_code'] ?? '',
            'parent'                  => $options['parent'] ?? null,
            'metadata'                => [
                'instance_id' => $options['instance_id'] ?? 0,
                'updated_at'  => $options['updated_at'] ?? time(),
            ],
        ];

        return $client->create([
            'index'   => $options['index'] ?? Schema::INDEX,
            'routing' => $options['routing'] ?? Schema::INDEX,
            'type'    => Schema::O_EVENT,
            'id'      => $options['id'] ?? ($autoId + 1),
            'body'    => $event,
            'parent'  => $options['parent']['id'] ?? $options['lo_id'],
            'refresh' => true,
        ]);
    }
}
