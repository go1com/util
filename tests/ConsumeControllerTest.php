<?php

namespace go1\util\tests;

use go1\util\consume\ConsumeController;
use go1\util\contract\ConsumerInterface;
use go1\util\schema\mock\UserMockTrait;
use go1\util\Text;
use go1\util\user\UserHelper;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use stdClass;
use Error;
use Throwable;

class ConsumeControllerTest extends UtilTestCase
{
    use UserMockTrait;

    const ROUTING_KEY = 'routingKey';
    private $c;

    public function setUp()
    {
        parent::setUp();
        $this->c = $this->getContainer();
        unset($GLOBALS['consumeCount']);
    }

    private function consumerClass(bool $isAware = true, Throwable $exception = null)
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
                return $this->isAware;
            }

            public function consume(string $routingKey, stdClass $body, stdClass $context = null): bool
            {
                if (!$this->exception) {
                    global $consumeCount;
                    $consumeCount[$routingKey] = isset($consumeCount[$routingKey]) ? ++$consumeCount[$routingKey] : 1;

                    return true;
                }

                throw $this->exception;
            }
        };
    }

    public function test403()
    {
        $fooConsumer = $this->consumerClass();
        $consume = new ConsumeController([$fooConsumer], $this->c['logger'], $this->c['access_checker']);

        $req = Request::create('/consume', 'POST');
        $req->request->replace(['routingKey' => self::ROUTING_KEY, 'body' => ['foo' => 'bar']]);
        $res = $consume->post($req);

        $this->assertEquals(403, $res->getStatusCode());
        global $consumeCount;
        $this->assertNull($consumeCount);
    }

    public function test204()
    {
        $fooConsumer = $this->consumerClass();
        $barConsumer = $this->consumerClass();
        $consume = new ConsumeController([$fooConsumer, $barConsumer], $this->c['logger'], $this->c['access_checker']);

        $req = Request::create('/consume?jwt=' . UserHelper::ROOT_JWT, 'POST');
        $req->request->replace([
            'routingKey' => self::ROUTING_KEY,
            'body'       => ['foo' => 'bar'],
        ]);
        $req->request->set('jwt.payload', Text::jwtContent(UserHelper::ROOT_JWT));
        $res = $consume->post($req);

        $this->assertEquals(204, $res->getStatusCode());
        global $consumeCount;
        $this->assertCount(1, $consumeCount);
        $this->assertArrayHasKey(self::ROUTING_KEY, $consumeCount);
        $this->assertEquals(2, $consumeCount[self::ROUTING_KEY]);
    }

    public function test500()
    {
        $fooConsumer = $this->consumerClass();
        $barConsumer = $this->consumerClass();
        $excConsumer = $this->consumerClass(true, (new Exception('foo')));
        $errConsumer = $this->consumerClass(true, (new Error('foo')));
        $consume = new ConsumeController([$fooConsumer, $barConsumer, $excConsumer, $errConsumer], $this->c['logger'], $this->c['access_checker']);

        $req = Request::create('/consume?jwt=' . UserHelper::ROOT_JWT, 'POST');
        $req->request->replace([
            'routingKey' => self::ROUTING_KEY,
            'body'       => ['foo' => 'bar'],
        ]);
        $req->request->set('jwt.payload', Text::jwtContent(UserHelper::ROOT_JWT));
        $res = $consume->post($req);

        $this->assertEquals(500, $res->getStatusCode());
        global $consumeCount;
        $this->assertCount(1, $consumeCount);
        $this->assertArrayHasKey(self::ROUTING_KEY, $consumeCount);
        $this->assertEquals(2, $consumeCount[self::ROUTING_KEY]);
    }

    public function test204Empty()
    {
        $fooConsumer = $this->consumerClass(false);
        $consume = new ConsumeController([$fooConsumer], $this->c['logger'], $this->c['access_checker']);

        $req = Request::create('/consume?jwt=' . UserHelper::ROOT_JWT, 'POST');
        $req->request->replace([
            'routingKey' => self::ROUTING_KEY,
            'body'       => ['foo' => 'bar'],
        ]);
        $req->request->set('jwt.payload', Text::jwtContent(UserHelper::ROOT_JWT));
        $res = $consume->post($req);

        $this->assertEquals(204, $res->getStatusCode());
        global $consumeCount;
        $this->assertNull($consumeCount);
    }
}
