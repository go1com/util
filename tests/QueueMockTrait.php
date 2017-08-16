<?php

namespace go1\util\tests;

use go1\clients\MqClient;
use Pimple\Container;

trait QueueMockTrait
{
    protected $queueMessages = [];

    protected function mockMqClient(Container $c)
    {
        $c->extend('go1.client.mq', function () {
            $mqClient = $this
                ->getMockBuilder(MqClient::class)
                ->disableOriginalConstructor()
                ->setMethods(['publish'])
                ->getMock();

            $mqClient
                ->expects($this->any())
                ->method('publish')
                ->willReturnCallback(function ($body, string $routingKey) {
                    $this->queueMessages[$routingKey][] = $body;
                });

            return $mqClient;
        });
    }
}
