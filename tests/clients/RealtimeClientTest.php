<?php

namespace go1\util\schema\tests;

use go1\clients\MqClient;
use go1\clients\RealtimeClient;
use go1\util\queue\Queue;
use go1\util\tests\UtilTestCase;
use HTMLPurifier;

class RealtimeClientTest extends UtilTestCase
{
    public function test()
    {
        $c = $this->getContainer();
        $c->extend('go1.client.realtime', function () use (&$c) {
            $mqClient = $this->getMockMqClient();

            $realtimeClient = $this
                ->getMockBuilder(RealtimeClient::class)
                ->setConstructorArgs([
                    $mqClient,
                    new HTMLPurifier(),
                    $c['realtime_url'],
                ])
                ->setMethods()
                ->getMock();

            return $realtimeClient;
        });

        /** @var RealtimeClient $client */
        $client = $c['go1.client.realtime'];
        $data = [
            'message'     => 'message',
            'image'       => 'image',
            'tag'         => 'tag',
            'from'        => 'from',
            'instance_id' => 1,
        ];
        $client->notify(1, $data);

        $this->assertCount(1, $this->queueMessages[Queue::DO_CONSUMER_HTTP_REQUEST]);
        $msg = json_decode($this->queueMessages[Queue::DO_CONSUMER_HTTP_REQUEST][0]['body']);
        $this->assertEquals($msg->pid, 1);
        $this->assertEquals($msg->message, 'message');
        $this->assertEquals($msg->image, 'image');
        $this->assertEquals($msg->tag, 'tag');
        $this->assertEquals($msg->from, 'from');
    }

    public function getMockMqClient()
    {
        $mqClient = $this
            ->getMockBuilder(MqClient::class)
            ->disableOriginalConstructor()
            ->setMethods(['publish'])
            ->getMock();

        $mqClient
            ->expects($this->any())
            ->method('publish')
            ->willReturnCallback(
                function (array $body, string $routingKey) {
                    if ($routingKey == Queue::DO_CONSUMER_HTTP_REQUEST) {
                        $this->queueMessages[$routingKey][] = $body;
                    }

                    return true;
                }
            );

        return $mqClient;
    }
}
