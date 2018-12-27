<?php

namespace go1\util\tests;

use go1\clients\MqClient;
use go1\util\publishing\event\EventInterface;
use Pimple\Container;

trait QueueMockTrait
{
    protected $queueMessages = [];

    protected function mockMqClient(Container $c, callable $callback = null)
    {
        $c->extend('go1.client.mq', function () use ($callback) {
            $queue = $this
                ->getMockBuilder(MqClient::class)
                ->disableOriginalConstructor()
                ->setMethods(['publish', 'queue', 'publishEvent'])
                ->getMock();

            $response = function ($body, string $routingKey, $context) use ($callback) {
                $callback && $callback($body, $routingKey, $context);
                if ($context) {
                    is_array($body) && $body['_context'] = $context;
                    is_object($body) && $body->_context = (object) $context;
                }
                $this->queueMessages[$routingKey][] = $body;
            };

            $queue
                ->expects($this->any())
                ->method('publish')
                ->willReturnCallback($response);

            $queue
                ->expects($this->any())
                ->method('queue')
                ->willReturnCallback($response);

            $responseEvent = function (EventInterface $event) use ($callback) {
                $callback && $callback($event);
                $body = $event->getPayload();
                is_array($body) && $body['_context'] = $event->getContext();
                is_object($body) && $body->_context = (object) $event->getContext();
                $this->queueMessages[$event->getSubject()][] = $body;
            };

            $queue
                ->expects($this->any())
                ->method('publishEvent')
                ->willReturnCallback($responseEvent);

            return $queue;
        });
    }
}
