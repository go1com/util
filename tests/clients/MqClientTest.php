<?php

namespace go1\util\schema\tests;

use go1\clients\MqClient;
use go1\util\tests\UtilTestCase;

class MqClientTest extends UtilTestCase
{
    public function dataMessage()
    {
        return [
            [
                (object)['foo' => 'bar'],
                'user.update',
                'Missing entity ID.',
            ], [
                (object)['id' => 1],
                'user.update',
                '',
            ], [
                (object)['id' => null],
                'user.update',
                'Missing entity ID.',
            ], [
                (object)[],
                'user.update',
                'Missing entity ID.',
            ], [
                ['foo' => 'bar'],
                'user.update',
                'Missing entity ID.',
            ], [
                ['id' => 1],
                'user.update',
                '',
            ], [
                ['id' => null],
                'user.update',
                'Missing entity ID.',
            ], [
                [],
                'user.update',
                'Missing entity ID.',
            ], [
                [],
                '',
                '',
            ],
        ];
    }

    /** @dataProvider dataMessage */
    public function testProcessMessage($messageBody, $routingKey, string $expectedString)
    {
        $obj = $this->getMockBuilder(MqClient::class)
            ->disableOriginalConstructor()
            ->getMock();
        $class = new \ReflectionClass(MqClient::class);
        $method = $class->getMethod('processMessage');
        $method->setAccessible(true);
        if ($expectedString) {
            $this->expectException(\Exception::class);
            $this->expectExceptionMessage($expectedString);
        }
        $messageBody = $method->invokeArgs($obj, [$messageBody, $routingKey]);
        !$expectedString && $this->assertEmpty($messageBody);
    }
}
