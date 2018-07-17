<?php

namespace go1\util\tests;

use go1\clients\MqClient;
use Pimple\Container;

trait QueueMockTrait
{
    protected $queueMessages = [];

    protected function mockMqClient(Container $c, callable $callback = null)
    {
        $c->extend('go1.client.mq', function () use ($callback) {
            $mqClient = $this
                ->getMockBuilder(MqClient::class)
                ->disableOriginalConstructor()
                ->setMethods(['publish', 'queue'])
                ->getMock();

            $response = function ($body, string $routingKey, $context) use ($callback) {
                $callback && $callback($body, $routingKey, $context);
                if ($context) {
                    is_array($body) && $body['_context'] = $context;
                    is_object($body) && $body->_context = (object) $context;
                }
                $this->queueMessages[$routingKey][] = $body;
            };

            $mqClient
                ->expects($this->any())
                ->method('publish')
                ->willReturnCallback($response);

            $mqClient
                ->expects($this->any())
                ->method('queue')
                ->willReturnCallback($response);

            return $mqClient;
        });
    }
}
