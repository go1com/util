<?php

namespace go1\util\es\mock;

use Elasticsearch\Client;
use go1\util\es\Schema;

trait EsInstallTrait
{
    public function installEs(Client $client, array $indices = [])
    {
        $settings = [
            'settings' => [
                'number_of_shards'   => 1,
                'number_of_replicas' => 0,
                'refresh_interval'   => -1,
            ],
        ];

        foreach ($indices as $index) {
            if (!$client->indices()->exists(['index' => $index])) {
                $client->indices()->create([
                    'index' => $index,
                    'body'  => Schema::BODY + $settings,
                ]);
            }
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
