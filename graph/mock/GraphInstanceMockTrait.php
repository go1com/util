<?php

namespace go1\util\graph\mock;

use GraphAware\Neo4j\Client\Client;

trait GraphInstanceMockTrait
{
    protected function createGraphInstance(Client $client, int $instanceId = null)
    {
        static $autoInstanceId;

        $instanceId = $instanceId ?: ++$autoInstanceId;

        $client->run(
            "MERGE (portal:Group { name: {portalName} })",
            ['portalName' => "portal:{$instanceId}"]
        );

        return $instanceId;
    }
}
