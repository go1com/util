<?php

namespace go1\util\tests;

use go1\util\consume\ConsumeController;
use go1\util\consume\Consumer;
use go1\util\edge\EdgeTypes;
use go1\util\Queue;
use go1\util\schema\mock\UserMockTrait;
use go1\util\Text;
use go1\util\user\UserHelper;
use Exception;
use Symfony\Component\HttpFoundation\Request;

class ConsumeControllerTest extends UtilTestCase
{
    use UserMockTrait;

    private $c;
    private $consume;
    private $fooConsumer;

    public function setUp()
    {
        parent::setUp();
        $this->c = $this->getContainer();

        $this->fooConsumer = $this->getMockBuilder(Consumer::class)
            ->setMethods(['aware', 'consume'])
            ->getMock();

        $this->fooConsumer
            ->method('aware')
            ->willReturn(true);

        $this->fooConsumer
            ->method('consume')
            ->willReturn(true);

        $this->consume = new ConsumeController([$this->fooConsumer], $this->c['logger'], $this->c['access_checker']);
    }

    public function test403()
    {
        $req = Request::create('/consume', 'POST');
        $req->request->replace(['routingKey' => Queue::RO_CREATE, 'body' => ['foo' => 'bar']]);
        $res = $this->consume->post($req);
        $this->assertEquals(403, $res->getStatusCode());
    }

    public function test200()
    {
        $req = Request::create('/consume?jwt=' . UserHelper::ROOT_JWT, 'POST');
        $req->request->replace([
            'routingKey' => Queue::RO_CREATE,
            'body'       => ['type' => EdgeTypes::HAS_SHARE_USER_NOTE, 'notify' => 1, 'weight' => 0],
        ]);
        $req->request->set('jwt.payload', Text::jwtContent(UserHelper::ROOT_JWT));
        $res = $this->consume->post($req);

        $this->assertEquals(204, $res->getStatusCode());
    }

    public function testError()
    {
        $this->fooConsumer
            ->expects($this->once())
            ->method('consume')
            ->willThrowException(new Exception);
        $this->expectException(Exception::class);

        $req = Request::create('/consume?jwt=' . UserHelper::ROOT_JWT, 'POST');
        $req->request->replace([
            'routingKey' => Queue::RO_CREATE,
            'body'       => ['type' => EdgeTypes::HAS_SHARE_USER_NOTE, 'notify' => 1, 'weight' => 0],
        ]);
        $req->request->set('jwt.payload', Text::jwtContent(UserHelper::ROOT_JWT));
        $res = $this->consume->post($req);

        $this->assertEquals(500, $res->getStatusCode());
    }
}
