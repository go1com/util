<?php

namespace go1\util\tests;

use go1\util\consume\ConsumeController;
use go1\util\contract\ConsumerInterface;
use go1\util\Queue;
use go1\util\schema\mock\UserMockTrait;
use go1\util\Text;
use go1\util\user\UserHelper;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use stdClass;
use Error;

class ConsumeControllerTest extends UtilTestCase
{
    use UserMockTrait;

    private $c;

    public function setUp()
    {
        parent::setUp();
        $this->c = $this->getContainer();
    }

    public function consumerClass($isAware = true, $exception = false)
    {
        return new class($isAware, $exception) implements ConsumerInterface
        {
            private $isAware;
            private $exception;

            public function __construct($isAware, $exception)
            {
                $this->isAware = $isAware;
                $this->exception = $exception;
            }

            public function aware(string $event): bool
            {
                return $this->isAware ? true : false;
            }

            public function consume(string $routingKey, stdClass $body): bool
            {
                if (!$this->exception) {
                    global $foo;
                    $foo[$routingKey] = isset($foo[$routingKey]) ? $foo[$routingKey]++ : 1;

                    return true;
                }

                throw $this->exception;
            }
        };
    }

    public function test403()
    {
        $fooConsumer = $this->consumerClass(false);
        $consume = new ConsumeController([$fooConsumer], $this->c['logger'], $this->c['access_checker']);

        $req = Request::create('/consume', 'POST');
        $req->request->replace(['routingKey' => Queue::RO_CREATE, 'body' => ['foo' => 'bar']]);
        $res = $consume->post($req);
        $this->assertEquals(403, $res->getStatusCode());
    }

    public function test204()
    {
        $fooConsumer = $this->consumerClass();
        $barConsumer = $this->consumerClass();
        $consume = new ConsumeController([$fooConsumer, $barConsumer], $this->c['logger'], $this->c['access_checker']);

        $req = Request::create('/consume?jwt=' . UserHelper::ROOT_JWT, 'POST');
        $req->request->replace([
            'routingKey' => Queue::RO_CREATE,
            'body'       => ['foo' => 'bar'],
        ]);
        $req->request->set('jwt.payload', Text::jwtContent(UserHelper::ROOT_JWT));
        $res = $consume->post($req);
        global $foo;

        $this->assertEquals(204, $res->getStatusCode());
        $this->assertCount(1, $foo);
        $this->assertArrayHasKey(Queue::RO_CREATE, $foo);
    }

    public function testException()
    {
        $fooConsumer = $this->consumerClass();
        $barConsumer = $this->consumerClass();
        $excConsumer = $this->consumerClass(true, (new Exception('foo')));
        $errConsumer = $this->consumerClass(true, (new Error('foo')));
        $consume = new ConsumeController([$fooConsumer, $barConsumer, $excConsumer, $errConsumer], $this->c['logger'], $this->c['access_checker']);

        $req = Request::create('/consume?jwt=' . UserHelper::ROOT_JWT, 'POST');
        $req->request->replace([
            'routingKey' => Queue::RO_CREATE,
            'body'       => ['foo' => 'bar'],
        ]);
        $req->request->set('jwt.payload', Text::jwtContent(UserHelper::ROOT_JWT));
        $res = $consume->post($req);
        global $foo;

        $this->assertEquals(500, $res->getStatusCode());
        $this->assertCount(1, $foo);
        $this->assertArrayHasKey(Queue::RO_CREATE, $foo);
    }

    public function testDontConsume()
    {
        $fooConsumer = $this->consumerClass(false);
        $consume = new ConsumeController([$fooConsumer], $this->c['logger'], $this->c['access_checker']);

        $req = Request::create('/consume?jwt=' . UserHelper::ROOT_JWT, 'POST');
        $req->request->replace([
            'routingKey' => Queue::RO_CREATE,
            'body'       => ['foo' => 'bar'],
        ]);
        $req->request->set('jwt.payload', Text::jwtContent(UserHelper::ROOT_JWT));
        $res = $consume->post($req);
        global $foo;

        $this->assertEquals(204, $res->getStatusCode());
        $this->assertCount(1, $foo);
        $this->assertArrayHasKey(Queue::RO_CREATE, $foo);
    }
}
