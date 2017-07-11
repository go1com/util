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
    private $consume;
    private $fooConsumer;
    private $barConsumer;
    private $excConsumer;
    private $errConsumer;

    public function setUp()
    {
        parent::setUp();
        $this->c = $this->getContainer();

        $consumerObj = (new class($isAware = true, $exception = false) implements ConsumerInterface
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
                return true;
            }

            public function consume(string $routingKey, stdClass $body): bool
            {
                if (!$this->exception) {
                    return true;
                }

                throw $this->exception;
            }
        });

        $this->fooConsumer = new $consumerObj(true, false);
        $this->barConsumer = new $consumerObj(true, false);
        $this->excConsumer = new $consumerObj(true, (new Exception('foo')));
        $this->errConsumer = new $consumerObj(true, (new Error('foo')));
    }

    public function test403()
    {
        $this->consume = new ConsumeController([$this->fooConsumer], $this->c['logger'], $this->c['access_checker']);

        $req = Request::create('/consume', 'POST');
        $req->request->replace(['routingKey' => Queue::RO_CREATE, 'body' => ['foo' => 'bar']]);
        $res = $this->consume->post($req);
        $this->assertEquals(403, $res->getStatusCode());
    }

    public function dataTest()
    {
        return [
            [Queue::RO_CREATE, ['foo' => 'bar']],
            [Queue::ENROLMENT_CREATE, ['foo' => 'bar']],
            [Queue::ENROLMENT_UPDATE, ['foo' => 'bar']],
            [Queue::ENROLMENT_DELETE, ['foo' => 'bar']],
        ];
    }

    /**
     * @dataProvider dataTest
     */
    public function test204($routingKey, $expectedBody)
    {
        $consume = new ConsumeController([$this->fooConsumer, $this->barConsumer], $this->c['logger'], $this->c['access_checker']);

        $req = Request::create('/consume?jwt=' . UserHelper::ROOT_JWT, 'POST');
        $req->request->replace([
            'routingKey' => $routingKey,
            'body'       => $expectedBody,
        ]);
        $req->request->set('jwt.payload', Text::jwtContent(UserHelper::ROOT_JWT));
        $res = $consume->post($req);

        $this->assertEquals(204, $res->getStatusCode());
    }

    /**
     * @dataProvider dataTest
     */
    public function testException($routingKey, $expectedBody)
    {
        $consume = new ConsumeController([$this->fooConsumer, $this->barConsumer, $this->excConsumer, $this->errConsumer], $this->c['logger'], $this->c['access_checker']);

        $req = Request::create('/consume?jwt=' . UserHelper::ROOT_JWT, 'POST');
        $req->request->replace([
            'routingKey' => $routingKey,
            'body'       => $expectedBody,
        ]);
        $req->request->set('jwt.payload', Text::jwtContent(UserHelper::ROOT_JWT));
        $res = $consume->post($req);

        $this->assertEquals(500, $res->getStatusCode());
    }

    /**
     * @dataProvider dataTest
     */
    public function testExceptions($routingKey, $expectedBody)
    {
        $consume = new ConsumeController([$this->excConsumer, $this->errConsumer, $this->errConsumer], $this->c['logger'], $this->c['access_checker']);

        $req = Request::create('/consume?jwt=' . UserHelper::ROOT_JWT, 'POST');
        $req->request->replace([
            'routingKey' => $routingKey,
            'body'       => $expectedBody,
        ]);
        $req->request->set('jwt.payload', Text::jwtContent(UserHelper::ROOT_JWT));
        $res = $consume->post($req);

        $this->assertEquals(500, $res->getStatusCode());
    }
}
