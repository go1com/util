<?php

namespace go1\util\schema\tests;

use Doctrine\Common\Cache\ArrayCache;
use go1\clients\PortalClient;
use go1\util\tests\UtilTestCase;
use go1\util\user\UserHelper;
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

    public function testDefaultMailTemplate()
    {
        $client = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMock();
        $cache = $this->getMockBuilder(ArrayCache::class)
            ->disableOriginalConstructor()
            ->setMethods(['contains'])
            ->getMock();

        $client
            ->method('get')
            ->with('http://portal.test.service/conf/foo.bar/mail-template/welcome?jwt=' . UserHelper::ROOT_JWT)
            ->willReturn(
                new Response(200, [], json_encode([
                    'data'     => ['subject' => 'defaultSubject', 'body' => 'defaultBody', 'html' => 'defaultHtml'],
                    'instance' => 'default',
                ]))
            );

        $portalClient = new PortalClient($client, 'http://portal.test.service', $cache);
        $template = $portalClient->mailTemplate('foo.bar', 'welcome', 'subject', 'body', 'html');

        $this->assertEquals($template->getSubject(), 'subject');
        $this->assertEquals($template->getBody(), 'body');
        $this->assertEquals($template->getHtml(), 'html');
    }
}
