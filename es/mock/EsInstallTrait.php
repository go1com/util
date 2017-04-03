<?php

namespace go1\util\es\mock;

use Elasticsearch\Client;
use go1\util\es\Schema;

trait EsInstallTrait
{
    public function installEs(Client $client)
    {
        if ($client->indices()->exists(['index' => Schema::INDEX])) {
            $client->indices()->delete(['index' => Schema::INDEX]);
        }

        if (!$client->indices()->exists(['index' => Schema::INDEX])) {
            $client->indices()->create(Schema::SCHEMA);
        }
    }
}
