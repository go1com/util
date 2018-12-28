<?php

namespace go1\util\schema\mock;

use Doctrine\DBAL\Connection;

trait LocationMockTrait
{
    public function createLocation(Connection $db, array $options = []): int
    {
        $options['data'] = isset($options['data'])
            ? (is_scalar($options['data']) ? json_decode($options['data'], true) : $options['data'])
            : ['static_map' => ['thumbnail' => '']];

        $db->insert('event_location', [
            'id'                      => $options['id'] ?? null,
            'title'                   => $options['title'] ?? 'GO1, Australia',
            'portal_id'               => $options['portal_id'] ?? 1,
            'country'                 => $options['country'] ?? null,
            'administrative_area'     => $options['administrative_area'] ?? null,
            'sub_administrative_area' => $options['sub_administrative_area'] ?? null,
            'locality'                => $options['locality'] ?? null,
            'dependent_locality'      => $options['dependent_locality'] ?? null,
            'thoroughfare'            => $options['thoroughfare'] ?? null,
            'premise'                 => $options['premise'] ?? null,
            'sub_premise'             => $options['sub_premise'] ?? null,
            'organisation_name'       => $options['organisation_name'] ?? null,
            'name_line'               => $options['name_line'] ?? null,
            'postal_code'             => $options['postal_code'] ?? null,
            'author_id'               => $options['author_id'] ?? null,
            'is_online'               => $options['is_online'] ?? 1,
            'latitude'                => $options['latitude'] ?? null,
            'longitude'               => $options['longitude'] ?? null,
            'published'               => $options['published'] ?? 1,
            'data'                    => json_encode($options['data']),
            'created_time'            => $options['created_time'] ?? time(),
            'updated_time'            => $options['updated_time'] ?? time(),
        ]);

        return $db->lastInsertId('event_location');
    }
}
