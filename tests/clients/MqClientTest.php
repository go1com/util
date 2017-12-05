<?php

namespace go1\util\schema\tests;

use go1\clients\MqClient;
use go1\util\schema\mock\UserMockTrait;
use go1\util\Service;
use go1\util\tests\UtilTestCase;
use go1\util\UtilServiceProvider;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use Pimple\Container;
use ReflectionClass;
use Symfony\Component\HttpFoundation\Request;
use ReflectionObject;
use Symfony\Component\HttpFoundation\RequestStack;
use PhpAmqpLib\Wire\AMQPTable;

class MqClientTest extends UtilTestCase
{
    use UserMockTrait;

    public function dataMessage()
    {
        return [
            [(object) ['foo' => 'bar'], 'user.update', 'Missing entity ID or original data.'],
            [(object) ['id' => 1, 'original'=> ['id']], 'user.update', ''],
            [(object) ['id' => null], 'user.update', 'Missing entity ID or original data.'],
            [(object) ['original' => null], 'user.update', 'Missing entity ID or original data.'],
            [(object) [], 'user.update', 'Missing entity ID or original data.'],
            [['foo' => 'bar'], 'user.update', 'Missing entity ID or original data.'],
            [['id' => 1, 'original'=> ['id']], 'user.update', ''],
            [['id' => null], 'user.update', 'Missing entity ID or original data.'],
            [['original' => null], 'user.update', 'Missing entity ID or original data.'],
            [[], 'user.update', 'Missing entity ID or original data.'],
            [[], '', ''],
            [[], 'do.enrolment.update', ''],
        ];
    }

    /** @dataProvider dataMessage */
    public function testProcessMessage($body, $routingKey, string $expectedString)
    {
        $queue = $this->getMockBuilder(MqClient::class)->disableOriginalConstructor()->getMock();
        $class = new ReflectionClass(MqClient::class);
        $method = $class->getMethod('processMessage');
        $method->setAccessible(true);
        if ($expectedString) {
            $this->expectException(\Exception::class);
            $this->expectExceptionMessage($expectedString);
        }
        $body = $method->invokeArgs($queue, [$body, $routingKey]);
        !$expectedString && $this->assertEmpty($body);
    }

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

            $channel
                ->expects($this->any())
                ->method('basic_publish')
                ->willReturnCallback(function (AMQPMessage $message, string $exchange, string $routingKey) {
                    $properties = $message->get_properties();

                    /* @var $_AMQPTable AMQPTable */
                    $_AMQPTable = $properties['application_headers'];
                    $context = $_AMQPTable->getNativeData();

                    $this->assertEquals('foo.bar', $routingKey);
                    $this->assertEquals('events', $exchange);
                    $this->assertEquals('X-foo', $context['request_id']);
                    $this->assertEquals(999, $context['actor_id']);

                });

            $rMqClient = new ReflectionObject($mqClient);
            $rChannel = $rMqClient->getProperty('channel');
            $rChannel->setAccessible(true);
            $rChannel->setValue($mqClient, $channel);

            return $mqClient;
        });

        $req = Request::create("/");
        $req->headers->add(['X-Request-Id' => 'X-foo']);
        $req->request->set('jwt.payload', $this->getPayload(['id' => 999]));

        $requestStack = new RequestStack();
        $requestStack->push($req);
        $container->offsetSet('request_stack', $requestStack);

        /* @var $mqClient MqClient*/
        $mqClient = $container['go1.client.mq'];
        $mqClient->publish(['foo' => 'bar'], 'foo.bar');
    }
}
