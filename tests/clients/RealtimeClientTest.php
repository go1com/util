<?php

namespace go1\util\schema\tests;

use go1\clients\RealtimeClient;
use go1\util\tests\UtilTestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use HTMLPurifier;

class RealtimeClientTest extends UtilTestCase
{
    public function test()
    {
        $c = $this->getContainer();

        $httpClient = $this
            ->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->setMethods(['post'])
            ->getMock();

        $httpClient
            ->expects($this->any())
            ->method('post')
            ->willReturnCallback(
                function (string $uri, array $options)  use ($c) {
                    $this->assertEquals("{$c['realtime_url']}/notification", $uri);
                    $this->assertEquals([
                            'headers' => [
                                'Content-Type' => 'application/json',
                            ],
                            'json'    => [
                                'pid'         => 1,
                                'message'     => 'message',
                                'image'       => 'image',
                                'tag'         => 'tag',
                                'from'        => 'from',
                                'instance_id' => 1,
                            ],
                        ]
                        , $options);

                    return new Response();
                }
            );


        $c->extend('go1.client.realtime', function () use (&$c, $httpClient) {

            return $this
                ->getMockBuilder(RealtimeClient::class)
                ->setConstructorArgs([
                    $httpClient,
                    new HTMLPurifier(),
                    $c['realtime_url'],
                ])
                ->setMethods()
                ->getMock();
        });

        /** @var RealtimeClient $client */
        $client = $c['go1.client.realtime'];
        $data = [
            'message'     => 'message',
            'image'       => 'image',
            'tag'         => 'tag',
            'from'        => 'from',
            'instance_id' => 1,
        ];
        $client->notify(1, $data);
    }
}
