<?php

namespace go1\util\es\mock;

use Elasticsearch\Client;
use go1\util\award\AwardStatuses;
use go1\util\DateTime;
use go1\util\es\Schema;

trait EsAwardMockTrait
{
    public function createEsAward(Client $client, $options = [])
    {
        static $esAwardId;

        $award = [
            'id'          => $options['id'] ?? ++$esAwardId,
            'revision_id' => $options['revision_id'] ?? $esAwardId,
            'title'       => $options['title'] ?? 'Foo award',
            'description' => $options['description'] ?? '',
            'image'       => $options['image'] ?? '',
            'user_id'     => $options['user_id'] ?? 0,
            'instance_id' => $options['instance_id'] ?? 0,
            'published'   => $options['published'] ?? AwardStatuses::PUBLISHED,
            'quantity'    => $options['quantity'] ?? null,
            'expire'      => $options['expire'] ?? null,
            'created'     => DateTime::formatDate($options['created'] ?? time()),
            'items_count' => $options['items_count'] ?? 0,
            'tags'        => $options['tags'] ?? [],
            'locale'      => $options['locale'] ?? '',
            'metadata'    => [
                'instance_id' => $options['instance_id'] ?? 0,
                'updated_at'  => $options['updated_at'] ?? time(),
            ],
        ];

        $client->create([
            'index'   => $options['index'] ?? Schema::INDEX,
            'routing' => $options['routing'] ?? Schema::INDEX,
            'type'    => Schema::O_AWARD,
            'id'      => $award['id'],
            'body'    => $award,
            'refresh' => true,
        ]);

        return $award['id'];
    }

    public function createEsAwardItemManual(Client $client, $options = [])
    {
        static $esAwardItemManualId;

        $awardItemManual = [
            'id'              => $options['id'] ?? ++$esAwardItemManualId,
            'entity_id'       => $options['entity_id'] ?? 0,
            'title'           => $options['title'] ?? null,
            'description'     => $options['description'] ?? null,
            'type'            => $options['type'] ?? null,
            'quantity'        => $options['quantity'] ?? 0,
            'completion_date' => DateTime::formatDate($options['completion_date'] ?? time()),
            'certificate'     => $options['certificate'] ?? null,
            'verified'        => $options['verified'] ?? 0,
            'weight'          => $options['weight'] ?? 0,
            'categories'      => $options['categories'] ?? [],
        ];

        $client->create([
            'index'   => $options['index'] ?? Schema::INDEX,
            'routing' => $options['routing'] ?? Schema::INDEX,
            'type'    => Schema::O_AWARD_ITEM_MANUAL,
            'id'      => $awardItemManual['id'],
            'parent'  => $options['award_id'],
            'body'    => $awardItemManual,
            'refresh' => true,
        ]);

        foreach ($awardItemManual['categories'] as $category) {
            if (!$category) {
                continue;
            }

            $client->update([
                'index'   => $options['index'] ?? Schema::INDEX,
                'routing' => $options['routing'] ?? Schema::INDEX,
                'type'    => Schema::O_SUGGESTION_CATEGORY,
                'id'      => md5($category),
                'body'    => [
                    'script' => [
                        'inline' => 'ctx._source.weight += 1',
                    ],
                    'upsert' => [
                        'category' => [
                            'input'  => $category,
                            'weight' => 1,
                        ],
                        'metadata' => [
                            'instance_id' => $options['instance_id'] ?? 0,
                        ],
                    ],
                ],
                'refresh' => true,
            ]);
        }

        return $awardItemManual['id'];
    }
}
