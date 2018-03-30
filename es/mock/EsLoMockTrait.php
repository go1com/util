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

        $options['data'] = isset($options['data'])
            ? (is_scalar($options['data']) ? json_decode($options['data'], true) : json_decode(json_encode($options['data']), true))
            : [];

        $loId = $options['id'] ?? ++$autoId;

        $event = $options['event'] ?? null;
        if (is_array($event) && $event) {
            $event = [
                'lo_id'                   => $loId,
                'id'                      => $event['id'] ?? null,
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
            ];
        }

        $lo = [
            'id'             => $loId,
            'type'           => $options['type'] ?? LoTypes::COURSE,
            'origin_id'      => $options['origin_id'] ?? 0,
            'status'         => $options['status'] ?? 0,
            'private'        => $options['private'] ?? 0,
            'published'      => $options['published'] ?? 1,
            'marketplace'    => $options['marketplace'] ?? 0,
            'sharing'        => $options['sharing'] ?? 0,
            'language'       => $options['language'] ?? 'en',
            'instance_id'    => $options['instance_id'] ?? 0,
            'portal_name'    => $options['portal_name'] ?? 'az.mygo1.com',
            'locale'         => $options['locale'] ?? 0,
            'title'          => $options['title'] ?? 'Foo course',
            'description'    => $options['description'] ?? '',
            'tags'           => (array) ($options['tags'] ?? []),
            'image'          => $options['image'] ?? '',
            'items_count'    => $options['items_count'] ?? 0,
            'pricing'        => [
                'currency'     => $options['currency'] ?? 'USD',
                'price'        => $options['price'] ?? 0.00,
                'tax'          => $options['tax'] ?? 0.00,
                'tax_included' => $options['tax_included'] ?? 1,
                'tax_display'  => $options['tax_display'] ?? 1,
            ],
            'duration'       => $options['duration'] ?? 0,
            'assessors'      => $options['assessors'] ?? [],
            'created'        => DateTime::formatDate($options['created'] ?? time()),
            'updated'        => DateTime::formatDate($options['updated'] ?? time()),
            'authors'        => $options['authors'] ?? [],
            'group_ids'      => $options['group_ids'] ?? [],
            'locations'      => $options['locations'] ?? [],
            'event'          => $event,
            'metadata'       => [
                'parents_authors_ids' => $options['metadata']['parents_authors_ids'] ?? null,
                'parents_id'          => $options['metadata']['parents_id'] ?? null,
                'instance_id'         => intval($options['routing'] ?? $options['instance_id'] ?? 0),
                'updated_at'          => $options['updated_at'] ?? time(),
                'customized'          => $options['metadata']['customized'] ?? 0,
                'shared'              => $options['metadata']['shared'] ?? 0,
                'shared_passive'      => $options['metadata']['shared_passive'] ?? null,
            ],
            'data'           => [
                'allow_resubmit' => $options['data']['allow_resubmit'] ?? null,
                'label'          => $options['data']['label'] ?? null,
                'pass_rate'      => $options['data']['pass_rate'] ?? null,
                'url'            => $options['data']['url'] ?? null,
            ],
            'totalEnrolment' => $options['totalEnrolment'] ?? 0,
            'vote'           => [
                'percent' => $options['vote']['percent'] ?? null,
                'rank'    => $options['vote']['rank'] ?? null,
                'like'    => $options['vote']['like'] ?? null,
                'dislike' => $options['vote']['dislike'] ?? null,
            ],
        ];

        $client->create([
            'index'   => $options['index'] ?? Schema::INDEX,
            'routing' => $options['routing'] ?? Schema::INDEX,
            'type'    => Schema::O_LO,
            'id'      => $options['_id'] ?? $lo['id'],
            'body'    => $lo,
            'refresh' => true,
        ]);

        foreach ($lo['tags'] as $tag) {
            if (!$tag) {
                continue;
            }

            $esLoId = (LoTypes::AWARD == $lo['type']) ? sprintf('%s:%s', LoTypes::AWARD, $loId) : $loId;
            $client->update([
                'index'   => $options['index'] ?? Schema::INDEX,
                'routing' => $options['routing'] ?? Schema::INDEX,
                'type'    => Schema::O_SUGGESTION_TAG,
                'id'      => md5($tag . $lo['instance_id']),
                'body'    => [
                    'script' => [
                        'inline' => implode(';', [
                            'ctx._source.metadata.lo_ids.add(params.esLoId)',
                            'ctx._source.tag.weight = ctx._source.metadata.lo_ids.length',
                        ]),
                        'params' => ['esLoId' => $esLoId],
                    ],
                    'upsert' => [
                        'tag' => [
                            'input'    => $tag,
                            'weight'   => 1,
                            'contexts' => ['instance_id' => $lo['instance_id']],
                        ],
                        'metadata' => [
                            'instance_id' => $options['instance_id'] ?? 0,
                            'lo_ids'      => (array) $esLoId,
                        ],
                    ],
                ],
                'refresh' => true,
            ]);
        }

        return $lo['id'];
    }
}
