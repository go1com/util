<?php

namespace go1\util\es\mock;

use Elasticsearch\Client;
use go1\util\DateTime;
use go1\util\es\Schema;
use go1\util\group\GroupItemStatus;
use go1\util\group\GroupStatus;
use go1\util\group\GroupTypes;

trait EsGroupMockTrait
{
    public function createEsGroup(Client $client, array $options = []): string
    {
        static $autoId = 1;

        $group = [
            'id'          => $options['id'] ?? ++$autoId,
            'title'       => $options['title'] ?? 'Foo',
            'portal_name' => $options['portal_name'] ?? 'Foo',
            'type'        => $options['type'] ?? GroupTypes::DEFAULT,
            'description' => $options['description'] ?? '',
            'image'       => $options['image'] ?? '',
            'user_id'     => $options['user_id'] ?? 0,
            'visibility'  => $options['visibility'] ?? GroupStatus::PUBLIC,
            'created'     => DateTime::formatDate($options['created'] ?? time()),
            'updated'     => DateTime::formatDate($options['updated'] ?? time()),
            'metadata'    => [
                'instance_id' => $options['instance_id'] ?? 0,
                'updated_at'  => $options['updated_at'] ?? time(),
            ],
        ];

        $client->create([
            'index'   => $options['index'] ?? Schema::INDEX,
            'routing' => $options['routing'] ?? Schema::INDEX,
            'type'    => Schema::O_GROUP,
            'id'      => $group['id'],
            'body'    => $group,
            'refresh' => true,
        ]);

        return $group['id'];
    }

    public function createEsGroupItem(Client $client, array $options = []): string
    {
        $options['id'] = $options['id'] ?? null;
        $client->create([
            'index'   => $options['index'],
            'type'    => Schema::O_GROUP_ITEM,
            'parent'  => $options['group_id'],
            'id'      => $options['id'],
            'body'    => [
                'id'          => $options['id'],
                'entity_type' => $options['entity_type'] ?? 'user',
                'entity_id'   => $options['entity_id'] ?? 1,
                'status'      => $options['status'] ?? GroupItemStatus::ACTIVE,
                'metadata'    => [
                    'instance_id' => $options['instance_id'] ?? 0,
                    'updated_at'  => $options['updated_at'] ?? time(),
                ],
            ],
            'refresh' => true,
        ]);

        return $options['id'];
    }
}
