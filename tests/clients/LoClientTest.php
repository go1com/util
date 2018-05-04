<?php

namespace go1\util\schema\tests;

use go1\clients\LoClient;
use go1\util\queue\Queue;
use go1\util\tests\QueueMockTrait;
use go1\util\tests\UtilTestCase;

class LoClientTest extends UtilTestCase
{
    use QueueMockTrait;

    public function testShareLo()
    {
        $c = $this->getContainer();
        $this->mockMqClient($c);

        /** @var LoClient $client */
        $client = $c['go1.client.lo'];
        $client->share(1000, 10000);

        $message = $this->queueMessages[Queue::DO_CONSUMER_HTTP_REQUEST][0];
        $this->assertEquals("POST", $message['method']);
        $this->assertEquals($c['lo_url'] . "/lo/10000/share/1000", $message['url']);
    }

    public function testUnShareLo()
    {
        $c = $this->getContainer();
        $this->mockMqClient($c);

        /** @var LoClient $client */
        $client = $c['go1.client.lo'];
        $client->share(1000, 10000, true);

        $message = $this->queueMessages[Queue::DO_CONSUMER_HTTP_REQUEST][0];
        $this->assertEquals("DELETE", $message['method']);
        $this->assertEquals($c['lo_url'] . "/lo/10000/share/1000", $message['url']);
    }
}
