<?php

namespace go1\util\tests\clients;

use go1\clients\MqClient;
use go1\util\tests\UtilTestCase;

class MqClientTest extends UtilTestCase
{
    public function dataMessage()
    {
        return [
            [
                ['id' => 1],
                ['actor_id' => 1],
                '{"id":1,"context":{"actor_id":1}}',
            ], [
                ['id' => 1],
                [],
                '{"id":1}',
            ], [
                'id',
                ['actor_id' => 1],
                'id',
            ], [
                'id',
                [],
                'id',
            ],
        ];
    }

    /** @dataProvider dataMessage */
    public function testProcessMessage($messageBody, $context, $message)
    {
        $obj = $this->getMockBuilder(MqClient::class)
            ->disableOriginalConstructor()
            ->getMock();

        $class = new \ReflectionClass(MqClient::class);
        $method = $class->getMethod('processMessage');
        $method->setAccessible(true);
        $messageBody = $method->invokeArgs($obj, [$messageBody, $context]);
        $this->assertEquals($messageBody, $message);
    }

    public function dataQueue()
    {
        return [
            [
                ['id' => 1],
                ['actor_id' => 1],
                ['id' => 1, 'context' => ['actor_id' => 1]],
            ], [
                ['id' => 1],
                [],
                ['id' => 1],
            ], [
                '{"id":1}',
                ['actor_id' => 1],
                ['id' => 1, 'context' => ['actor_id' => 1]],
            ], [
                '{"id":1}',
                [],
                '{"id":1}',
            ],
        ];
    }

    /** @dataProvider dataQueue */
    public function testProcessQueue($messageBody, $context, $message)
    {
        $obj = $this->getMockBuilder(MqClient::class)
            ->disableOriginalConstructor()
            ->getMock();

        $class = new \ReflectionClass(MqClient::class);
        $method = $class->getMethod('processQueue');
        $method->setAccessible(true);
        $messageBody = $method->invokeArgs($obj, [$messageBody, $context]);
        $this->assertEquals($messageBody, $message);
    }
}
