<?php

namespace go1\util\es\mock;

use Elasticsearch\Client;
use go1\util\es\Schema;

trait EsInstallTrait
{
    public function installEs(Client $client)
    {
        $settings = [
            'settings' => [
                'number_of_shards'   => 1,
                'number_of_replicas' => 0,
            ]
        ];

        if ($client->indices()->exists(['index' => Schema::INDEX])) {
            $client->indices()->delete(['index' => Schema::ALL_INDEX]);
        }

        if (!$client->indices()->exists(['index' => Schema::INDEX])) {
            $client->indices()->create([
                'index' => Schema::INDEX,
                'body'  => Schema::BODY + $settings,
            ]);
        }

        if (!$client->indices()->exists(['index' => Schema::MARKETPLACE_INDEX])) {
            $client->indices()->create([
                'index' => Schema::MARKETPLACE_INDEX,
                'body'  => Schema::BODY + $settings,
            ]);
        }

        if (!$client->indices()->exists(['index' => Schema::ACTIVITY_INDEX])) {
            $client->indices()->create([
                'index' => Schema::ACTIVITY_INDEX,
                'body'  => Schema::BODY + $settings,
            ]);
        }
    }

    public function installPortalIndex(Client $client, int $portalId)
    {
        if (!$client->indices()->exists($params = ['index' => $portalIndex = Schema::portalIndex($portalId)])) {
            $params['body']['actions'][]['add'] = [
                'index'   => Schema::INDEX,
                'alias'   => $portalIndex,
                'routing' => $portalId,
                'filter'  => [
                    'term' => [
                        'metadata.instance_id' => $portalId
                    ]
                ],
            ];
            $client->indices()->updateAliases($params);
        }
    }
}
