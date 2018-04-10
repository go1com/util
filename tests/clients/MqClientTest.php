<?php

namespace go1\util\schema\tests;

use go1\clients\MqClient;
use go1\util\schema\mock\UserMockTrait;
use go1\util\Service;
use go1\util\tests\UtilTestCase;
use go1\util\UtilServiceProvider;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;
use Pimple\Container;
use ReflectionObject;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class MqClientTest extends UtilTestCase
{
    use UserMockTrait;

    public function testInjectRequest()
    {
        $container = (new Container(['accounts_name' => 'accounts.test']))
            ->register(new UtilServiceProvider, [
                'queueOptions' => Service::queueOptions(),
            ]);

        $container->extend('go1.client.mq', function (MqClient $mqClient) {
            $channel = $this
                ->getMockBuilder(AMQPChannel::class)
                ->disableOriginalConstructor()
                ->setMethods(['basic_publish'])
                ->getMock();

            $timestamp = time();
            $channel
                ->expects($this->any())
                ->method('basic_publish')
                ->willReturnCallback(function (AMQPMessage $message, string $exchange, string $routingKey) use ($timestamp) {
                    $properties = $message->get_properties();

                    /* @var $_AMQPTable AMQPTable */
                    $_AMQPTable = $properties['application_headers'];
                    $context = $_AMQPTable->getNativeData();

                    $this->assertEquals('foo.bar', $routingKey);
                    $this->assertEquals('events', $exchange);
                    $this->assertEquals('X-foo', $context['request_id']);
                    $this->assertEquals(999, $context['actor_id']);
                    $this->assertEquals($timestamp, $context[MqClient::CONTEXT_TIMESTAMP]);
                });

            $rMqClient = new ReflectionObject($mqClient);
            $rChannel = $rMqClient->getProperty('channel');
            $rChannel->setAccessible(true);
            $rChannel->setValue($mqClient, $channel);

            return $mqClient;
        });

        $req = Request::create("/");
        $req->headers->add(['X-Request-Id' => 'X-foo']);
        $req->attributes->set('jwt.payload', $this->getPayload(['id' => 999]));

        $requestStack = new RequestStack();
        $requestStack->push($req);
        $container->offsetSet('request_stack', $requestStack);

        /* @var $mqClient MqClient */
        $mqClient = $container['go1.client.mq'];
        $mqClient->publish(['foo' => 'bar'], 'foo.bar');
    }
}
