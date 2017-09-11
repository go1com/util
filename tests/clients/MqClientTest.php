<?php

namespace go1\util\schema\tests;

use go1\clients\MqClient;
use go1\util\tests\UtilTestCase;
use ReflectionClass;

class MqClientTest extends UtilTestCase
{
    public function dataMessage()
    {
        return [
            [(object) ['foo' => 'bar'], 'user.update', 'Missing entity ID.'],
            [(object) ['id' => 1], 'user.update', ''],
            [(object) ['id' => null], 'user.update', 'Missing entity ID.'],
            [(object) [], 'user.update', 'Missing entity ID.'],
            [['foo' => 'bar'], 'user.update', 'Missing entity ID.'],
            [['id' => 1], 'user.update', ''],
            [['id' => null], 'user.update', 'Missing entity ID.'],
            [[], 'user.update', 'Missing entity ID.'],
            [[], '', ''],
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
}
