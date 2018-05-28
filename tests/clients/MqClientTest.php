<?php

namespace go1\util\schema\tests;

use Exception;
use go1\clients\MqClient;
use go1\util\queue\Queue;
use go1\util\schema\mock\UserMockTrait;
use go1\util\Service;
use go1\util\tests\UtilTestCase;
use go1\util\UtilServiceProvider;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;
use Pimple\Container;
use ReflectionClass;
use ReflectionObject;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\PropertyAccess\PropertyAccess;

class MqClientTest extends UtilTestCase
{
    use UserMockTrait;

    public function dataMessage()
    {
        return [
            [(object) ['foo' => 'bar'], 'user.update', 'Missing entity ID or original data.'],
            [(object) ['id' => 1, 'original' => ['id']], 'user.update', ''],
            [(object) ['id' => null], 'user.update', 'Missing entity ID or original data.'],
            [(object) ['original' => null], 'user.update', 'Missing entity ID or original data.'],
            [(object) [], 'user.update', 'Missing entity ID or original data.'],
            [['foo' => 'bar'], 'user.update', 'Missing entity ID or original data.'],
            [['id' => 1, 'original' => ['id']], 'user.update', ''],
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

        $rPropertyAccessor = $class->getProperty('propertyAccessor');
        $rPropertyAccessor->setAccessible(true);
        $rPropertyAccessor->setValue($queue, $propertyAccessor = PropertyAccess::createPropertyAccessor());

        $method = $class->getMethod('processMessage');
        $method->setAccessible(true);
        if ($expectedString) {
            $this->expectException(Exception::class);
            $this->expectExceptionMessage($expectedString);
        }
        $body = $method->invokeArgs($queue, [$body, $routingKey]);
        !$expectedString && $this->assertEmpty($body);
    }

    public function testInjectRequest()
    {
        $c = (new Container(['accounts_name' => 'accounts.test']))
            ->register(new UtilServiceProvider, ['queueOptions' => Service::queueOptions()]);

        $c->extend('go1.client.mq', function (MqClient $queue) {
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

                    /* @var $context AMQPTable */
                    $context = $properties['application_headers'];
                    $context = $context->getNativeData();

                    $this->assertEquals('foo.bar', $routingKey);
                    $this->assertEquals('events', $exchange);
                    $this->assertEquals('X-foo', $context['request_id']);
                    $this->assertEquals(999, $context['actor_id']);
                    $this->assertEquals($timestamp, $context[MqClient::CONTEXT_TIMESTAMP]);
                });

            $rQueue = new ReflectionObject($queue);
            $rChannels = $rQueue->getProperty('channels');
            $rChannels->setAccessible(true);
            $channels['events']['topic'] = $channel;
            $rChannels->setValue($queue, $channels);

            return $queue;
        });

        $req = Request::create("/");
        $req->headers->add(['X-Request-Id' => 'X-foo']);
        $req->attributes->set('jwt.payload', $this->getPayload(['id' => 999]));

        $requestStack = new RequestStack();
        $requestStack->push($req);
        $c->offsetSet('request_stack', $requestStack);

        /* @var $queue MqClient */
        $queue = $c['go1.client.mq'];
        $queue->publish(['foo' => 'bar'], 'foo.bar');
    }

    public function testDefaultContext()
    {
        $c = (new Container(['accounts_name' => 'accounts.test']))
            ->register(new UtilServiceProvider, ['queueOptions' => Service::queueOptions()]);

        $expected = [
            Queue::PORTAL_CREATE => [
                MqClient::CONTEXT_PORTAL_NAME => '123',
                MqClient::CONTEXT_ENTITY_TYPE => 'portal',
            ],
            Queue::LO_CREATE => [
                MqClient::CONTEXT_PORTAL_NAME => '456',
                MqClient::CONTEXT_ENTITY_TYPE => 'lo',
            ],
            Queue::ENROLMENT_CREATE => [
                MqClient::CONTEXT_PORTAL_NAME => '457',
                MqClient::CONTEXT_ENTITY_TYPE => 'enrolment',
            ],
            Queue::PLAN_CREATE => [
                MqClient::CONTEXT_PORTAL_NAME => '458',
                MqClient::CONTEXT_ENTITY_TYPE => 'plan',
            ],
        ];

        $c->extend('go1.client.mq', function (MqClient $queue) use ($expected) {
            $channel = $this
                ->getMockBuilder(AMQPChannel::class)
                ->disableOriginalConstructor()
                ->setMethods(['basic_publish'])
                ->getMock();

            $timestamp = time();
            $channel
                ->expects($this->any())
                ->method('basic_publish')
                ->willReturnCallback(function (AMQPMessage $message, string $exchange, string $routingKey) use ($timestamp, $expected) {
                    $properties = $message->get_properties();

                    /* @var $context AMQPTable */
                    $context = $properties['application_headers'];
                    $context = $context->getNativeData();

                    $this->assertEquals('events', $exchange);
                    $this->assertEquals('X-foo', $context['request_id']);
                    $this->assertEquals(999, $context['actor_id']);
                    $this->assertEquals($timestamp, $context[MqClient::CONTEXT_TIMESTAMP]);

                    foreach ($expected as $key => $expectedValue) {
                        if ($key == $routingKey) {
                            $this->assertEquals($expectedValue[MqClient::CONTEXT_PORTAL_NAME], $context[MqClient::CONTEXT_PORTAL_NAME]);
                            $this->assertEquals($expectedValue[MqClient::CONTEXT_ENTITY_TYPE], $context[MqClient::CONTEXT_ENTITY_TYPE]);
                        }
                    }
                });

            $rQueue = new ReflectionObject($queue);
            $rChannels = $rQueue->getProperty('channels');
            $rChannels->setAccessible(true);
            $channels['events']['topic'] = $channel;
            $rChannels->setValue($queue, $channels);

            return $queue;
        });

        $req = Request::create("/");
        $req->headers->add(['X-Request-Id' => 'X-foo']);
        $req->attributes->set('jwt.payload', $this->getPayload(['id' => 999]));

        $requestStack = new RequestStack();
        $requestStack->push($req);
        $c->offsetSet('request_stack', $requestStack);

        /* @var $queue MqClient */
        $queue = $c['go1.client.mq'];
        $queue->publish((object)['title' => $expected[Queue::PORTAL_CREATE][MqClient::CONTEXT_PORTAL_NAME]], Queue::PORTAL_CREATE);
        $queue->publish((object)['taken_instance_id' => $expected[Queue::ENROLMENT_CREATE][MqClient::CONTEXT_PORTAL_NAME]], Queue::ENROLMENT_CREATE);
        $queue->publish((object)['instance_id' => $expected[Queue::LO_CREATE][MqClient::CONTEXT_PORTAL_NAME]], Queue::LO_CREATE);
        $queue->publish((object)['instance_id' => $expected[Queue::PLAN_CREATE][MqClient::CONTEXT_PORTAL_NAME]], Queue::PLAN_CREATE);
    }
}
