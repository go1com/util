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
}
