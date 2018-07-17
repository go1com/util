<?php

namespace go1\util\es\mock;

use Elasticsearch\Client;
use go1\util\DateTime;
use go1\util\es\Schema;

trait EsInstanceMockTrait
{
    public function createEsInstance(Client $client, $options = [])
    {
        static $autoId;

        $portal = [
            'id'            => $options['id'] ?? ++$autoId,
            'name'          => $options['name'] ?? 'GO1',
            'title'         => $options['title'] ?? 'az.mygo1.com',
            'status'        => $options['status'] ?? 1,
            'logo'          => $options['logo'] ?? null,
            'version'       => $options['version'] ?? '',
            'created'       => DateTime::formatDate($options['created'] ?? time()),
            'configuration' => $options['configuration'] ?? null,
        ];

        return $client->create([
            'index'   => Schema::INDEX,
            'routing' => Schema::INDEX,
            'type'    => Schema::O_PORTAL,
            'id'      => $portal['id'],
            'body'    => $portal,
            'parent'  => $portal['parent'] ?? 1,
        ]);
    }
}
