<?php

namespace go1\util\es\mock;

use Elasticsearch\Client;
use go1\util\es\IndexHelper;
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

        if ($client->indices()->exists(['index' => Schema::I_GO1])) {
            $client->indices()->delete(['index' => Schema::I_ALL]);
        }

        if (!$client->indices()->exists(['index' => Schema::I_GO1])) {
            $client->indices()->create([
                'index' => Schema::I_GO1,
                'body'  => Schema::BODY + $settings,
            ]);
        }

        if (!$client->indices()->exists(['index' => Schema::I_MARKETPLACE])) {
            $client->indices()->create([
                'index' => Schema::I_MARKETPLACE,
                'body'  => Schema::BODY + $settings,
            ]);
        }

        if (!$client->indices()->exists(['index' => Schema::I_ACTIVITY])) {
            $client->indices()->create([
                'index' => Schema::I_ACTIVITY,
                'body'  => [
                        'mappings' => Schema::I_ACTIVITY_MAPPING,
                ] + $settings,
            ]);
        }

        if (!$client->indices()->exists(['index' => Schema::I_MY_TEAM])) {
            $client->indices()->create([
                'index' => Schema::I_MY_TEAM,
                'body'  => [
                    'mappings' => Schema::I_MY_TEAM_MAPPING,
                ] + $settings,
            ]);
        }
    }

    public function installPortalIndex(Client $client, int $portalId)
    {
        if (!$client->indices()->exists($params = ['index' => $portalIndex = IndexHelper::portalIndex($portalId)])) {
            $params['body']['actions'][]['add'] = [
                'indices' => [Schema::I_GO1, Schema::I_MY_TEAM, Schema::I_ACTIVITY],
                'alias'   => $portalIndex,
                'routing' => $portalId,
                'filter'  => [
                    'term' => [
                        'metadata.instance_id' => $portalId,
                    ],
                ],
            ];
            $client->indices()->updateAliases($params);
        }
    }
}
