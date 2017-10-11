<?php

namespace go1\util\schema\tests;

use Doctrine\Common\Cache\ArrayCache;
use go1\clients\PortalClient;
use go1\util\tests\UtilTestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use Psr\Http\Message\RequestInterface;
use GuzzleHttp\Psr7\Response;

class PortalClientTest extends UtilTestCase
{
    public function test404()
    {
        $client = $this->getMockBuilder(Client::class)->setMethods(['request'])->getMock();
        $client
            ->expects($this->once())
            ->method('request')
            ->willThrowException(new BadResponseException(
                'Portal not found',
                $this->createMock(RequestInterface::class),
                new Response(404, [], '"Portal not found"')
            ));

        $cache = $this->getMockBuilder(ArrayCache::class)->setMethods(['contains'])->getMock();
        $cache
            ->expects($this->once())
            ->method('contains')
            ->willReturn(false);

        $portalClient = new PortalClient($client, 'http://portal.test.service', $cache);
        $response = $portalClient->load(123);

        $this->assertEmpty($response);
    }
}
