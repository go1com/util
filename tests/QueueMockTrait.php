<?php

namespace go1\util\tests;

use go1\clients\MqClient;
use go1\lo\App;

trait QueueMockTrait
{
    protected $queueMessages = [];

    protected function mockMqClient(App $app)
    {
        $app->extend('go1.client.mq', function () {
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
