<?php

namespace go1\util\tests;

use go1\util\consume\ConsumeController;
use go1\util\consume\Consumer;
use go1\util\contract\ConsumerInterface;
use go1\util\Queue;
use go1\util\schema\mock\UserMockTrait;
use go1\util\Text;
use go1\util\user\UserHelper;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use stdClass;

class ConsumeControllerTest extends UtilTestCase
{
    use UserMockTrait;

    private $c;
    private $consume;
    private $fooConsumer;
    private $barConsumer;
    private $errConsumer;

    public function setUp()
    {
        parent::setUp();
        $this->c = $this->getContainer();

        $this->fooConsumer = $this->builder();
        $this->barConsumer = $this->builder();
        $this->errConsumer = $this->builder(true, true);

    }

    private function builder($aware = true, $exception = false)
    {
        $consumerObj = new class implements ConsumerInterface
        {
            public function aware(string $event): bool
            {
                return true;
            }

            public function consume(string $routingKey, stdClass $body): bool
            {
                return true;
            }
        };

        $consumer = $this->getMockBuilder(Consumer::class)
            ->setMethods(['aware', 'consume'])
            ->getMock();

        $consumer
            ->method('aware')
            ->willReturn($aware);

        if ($exception) {
            $consumer
                ->method('consume')
                ->willThrowException(new Exception());

        }
        else {
            $consumer
                ->method('consume')
                ->willReturn($exception);
        }

        return $consumer;
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
        $this->consume = new ConsumeController([$this->fooConsumer, $this->barConsumer], $this->c['logger'], $this->c['access_checker']);

        $req = Request::create('/consume?jwt=' . UserHelper::ROOT_JWT, 'POST');
        $req->request->replace([
            'routingKey' => $routingKey,
            'body'       => $expectedBody,
        ]);
        $req->request->set('jwt.payload', Text::jwtContent(UserHelper::ROOT_JWT));
        $res = $this->consume->post($req);

        $this->assertEquals(204, $res->getStatusCode());
    }

    /**
     * @dataProvider dataTest
     */
    public function testException($routingKey, $expectedBody)
    {
        $this->consume = new ConsumeController([$this->fooConsumer, $this->barConsumer, $this->errConsumer], $this->c['logger'], $this->c['access_checker']);

        $req = Request::create('/consume?jwt=' . UserHelper::ROOT_JWT, 'POST');
        $req->request->replace([
            'routingKey' => $routingKey,
            'body'       => $expectedBody,
        ]);
        $req->request->set('jwt.payload', Text::jwtContent(UserHelper::ROOT_JWT));
        $res = $this->consume->post($req);

        $this->assertEquals(500, $res->getStatusCode());
    }

    /**
     * @dataProvider dataTest
     */
    public function testExceptions($routingKey, $expectedBody)
    {
        $this->consume = new ConsumeController([$this->errConsumer, $this->errConsumer, $this->errConsumer], $this->c['logger'], $this->c['access_checker']);

        $req = Request::create('/consume?jwt=' . UserHelper::ROOT_JWT, 'POST');
        $req->request->replace([
            'routingKey' => $routingKey,
            'body'       => $expectedBody,
        ]);
        $req->request->set('jwt.payload', Text::jwtContent(UserHelper::ROOT_JWT));
        $res = $this->consume->post($req);

        $this->assertEquals(500, $res->getStatusCode());
    }
}
