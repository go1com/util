<?php

namespace go1\util\schema\tests;

use go1\clients\Neo4jBuilderClient;
use go1\util\tests\UtilTestCase;

class Neo4jBuilderClientTest extends UtilTestCase
{
    public function test()
    {
        $client = new Neo4jBuilderClient();

        $query = $client->match('u.User')
            ->where('u.id', 'id')
            ->andWhere('u.mail', 'mail')
            ->skip(0)
            ->limit(10)
            ->setParameters([
                'id'   => 10,
                'mail' => 'abc@go1.com'
            ])
            ->execute();

        $this->assertEquals("MATCH u.User WHERE u.id = 10 AND u.mail = 'abc@go1.com' SKIP 0 LIMIT 10", $query);
    }
}
