<?php

namespace go1\util\schema\tests;

use go1\clients\MqClient;
use go1\util\queue\Queue;
use go1\util\tests\UtilTestCase;

class UserClientTest extends UtilTestCase
{
    public function test()
    {
        $c = $this->getContainer();

        $mqClient = $this
            ->getMockBuilder(MqClient::class)
            ->disableOriginalConstructor()
            ->setMethods(['publish'])
            ->getMock();

        $mqClient
            ->expects($this->any())
            ->method('publish')
            ->willReturnCallback(
                function (array $body, string $routingKey) {
                    if ($routingKey == Queue::DO_USER_UNBLOCK_MAIL) {
                        $this->assertEquals($body['mail'], 'abc@mail.com');
                    }

                    if ($routingKey == Queue::DO_USER_UNBLOCK_IP) {
                        $this->assertEquals($body['ip'], '192.168.0.1');
                    }

                    return true;
                }
            );

        $c['go1.client.mq'] = $mqClient;
        $c['go1.client.user']->unblockEmail('abc@mail.com');
        $c['go1.client.user']->unblockIp('192.168.0.1');
    }
}
