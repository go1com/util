<?php

namespace go1\util\es\mock;

use Elasticsearch\Client;
use go1\util\DateTime;
use go1\util\es\Schema;
use go1\util\lo\LoTypes;

trait EsLoMockTrait
{
    public function createEsLo(Client $client, $options = [])
    {
        static $autoId = 1;

        $lo = [
            'id'             => $options['id'] ?? ++$autoId,
            'type'           => $options['type'] ?? LoTypes::COURSE,
            'origin_id'      => $options['origin_id'] ?? 0,
            'status'         => $options['status'] ?? 0,
            'private'        => $options['private'] ?? 0,
            'published'      => $options['published'] ?? 1,
            'marketplace'    => $options['marketplace'] ?? 0,
            'sharing'        => $options['sharing'] ?? 0,
            'language'       => $options['language'] ?? 'en',
            'instance_id'    => $options['instance_id'] ?? 0,
            'locale'         => $options['locale'] ?? 0,
            'title'          => $options['title'] ?? 'Foo course',
            'description'    => $options['description'] ?? '',
            'tags'           => $options['tags'] ?? [],
            'image'          => $options['image'] ?? '',
            'items_count'    => $options['items_count'] ?? 0,
            'pricing'        => [
                'currency'     => $options['currency'] ?? 'USD',
                'price'        => $options['price'] ?? 0.00,
                'tax'          => $options['tax'] ?? 0.00,
                'tax_included' => $options['tax_included'] ?? true,
            ],
            'duration'       => $options['duration'] ?? 0,
            'assessors'      => $options['assessors'] ?? [],
            'created'        => DateTime::formatDate($options['created'] ?? time()),
            'updated'        => DateTime::formatDate($options['updated'] ?? time()),
            'authors'        => $options['authors'] ?? [],
            'group_ids'      => $options['group_ids'] ?? [],
            'metadata'       => [
                'parents_authors_ids' => $options['metadata']['parents_authors_ids'] ?? null,
                'parents_id'          => $options['metadata']['parents_id'] ?? null,
                'instance_id'         => $options['routing'] ?? $options['instance_id'] ?? 0,
                'updated_at'          => $options['updated_at'] ?? time(),
            ],
            'totalEnrolment' => $options['totalEnrolment'] ?? 0,
        ];

        $client->create([
            'index'   => $options['index'] ?? Schema::INDEX,
            'routing' => $options['routing'] ?? Schema::INDEX,
            'type'    => Schema::O_LO,
            'id'      => $lo['id'],
            'body'    => $lo,
            'refresh' => true
        ]);

        return $lo['id'];
    }
}
