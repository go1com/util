<?php

namespace go1\util\es\mock;

use Elasticsearch\Client;
use go1\util\DateTime;
use go1\util\es\Schema;
use go1\util\lo\LoTypes;

trait EsActivityMockTrait
{
    public function createEsActivity(Client $client, $options = [])
    {
        static $autoId = 1;

        $options['data'] = isset($options['data'])
            ? (is_scalar($options['data']) ? json_decode($options['data'], true) : json_decode(json_encode($options['data']), true))
            : [];

        $esActivityId = $options['id'] ?? ++$autoId;

        $activity = [
            'id'          => $options['id'] ?? ++$esActivityId,
            'instance_id' => $options['instance_id'] ?? 0,
            'actor_id'    => $options['actor_id'] ?? 0,
            'user_id'     => $options['user_id'] ?? 0,
            'entity_type' => $options['entity_type'] ?? 'lo',
            'entity_id'   => $options['entity_id'] ?? 0,
            'action_id'   => $options['action_id'] ?? 0,
            'tags'        => $options['tags'] ?? [],
            'created'     => DateTime::formatDate($options['created'] ?? time()),
            'updated'     => DateTime::formatDate($options['updated'] ?? time()),
            'context'     => [
                'actor'  => $options['context']['actor'] ?? null,
                'user'   => $options['context']['user'] ?? null,
                'entity' => $options['context']['entity'] ?? null,
                'diff'   => $options['context']['diff'] ?? [],
            ],
        ];

        $client->create([
            'index'   => $options['index'] ?? Schema::ACTIVITY_INDEX,
            'routing' => $options['instance_id'] ?? Schema::ACTIVITY_INDEX,
            'type'    => Schema::O_ACTIVITY,
            'id'      => $options['_id'] ?? $activity['id'],
            'body'    => $activity,
            'refresh' => true
        ]);

        return $activity['id'];
    }
}
