<?php

namespace go1\util\location\mock;

use Doctrine\DBAL\Connection;

trait LocationMockTrait
{
    public function createLocation(Connection $db, array $options = []): int
    {
        $db->insert('gc_location', [
            'title'                     => $options['title'] ?? 'Foo',
            'instance_id'               => $options['instance_id'] ?? 1,
            'country'                   => $options['country'] ?? 'AU',
            'administrative_area'       => $options['administrative_area'] ?? null,
            'sub_administrative_area'   => $options['sub_administrative_area'] ?? null,
            'locality'                  => $options['locality'] ?? null,
            'dependent_locality'        => $options['dependent_locality'] ?? null,
            'thoroughfare'              => $options['thoroughfare'] ?? null,
            'premise'                   => $options['premise'] ?? null,
            'sub_premise'               => $options['sub_premise'] ?? null,
            'organisation_name'         => $options['organisation_name'] ?? null,
            'name_line'                 => $options['name_line'] ?? null,
            'postal_code'               => $options['postal_code'] ?? null,
            'author_id'                 => $options['author_id'] ?? null,
            'created'                   => $options['created'] ?? time(),
            'updated'                   => $options['updated'] ?? time(),
        ]);

        return $db->lastInsertId('gc_location');
    }
}
