<?php

namespace go1\util\tests;

use Error;
use Exception;
use go1\clients\MqClient;
use go1\util\consume\ConsumeController;
use go1\util\contract\ConsumerInterface;
use go1\util\schema\mock\UserMockTrait;
use go1\util\Text;
use go1\util\user\UserHelper;
use stdClass;
use Symfony\Component\HttpFoundation\Request;
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

    private function consumerClass(bool $aware = true, Throwable $exception = null)
    {
        return new class($aware, $exception) implements ConsumerInterface
        {
            private $aware;
            private $exception;

            public function __construct($isAware, $exception)
            {
                $this->aware = $isAware;
                $this->exception = $exception;
            }

            public function aware(string $event): bool
            {
                return $this->aware;
            }

            public function consume(string $routingKey, stdClass $body, stdClass $context = null): bool
            {
                global $consumeCount;

                if (!$this->exception) {
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

    public function data204()
    {
        return [
            [(object) ['foo' => 'bar'], null],
            [['foo' => 'bar'], null],
            [(object) ['foo' => 'bar'], (object) ['foo' => 'bar']],
            [['foo' => 'bar'], ['foo' => 'bar']],
            [['foo' => 'bar'], []],
        ];
    }

    /** @dataProvider data204 */
    public function test204($body, $context)
    {
        global $consumeCount;

        $fooConsumer = $this->consumerClass();
        $barConsumer = $this->consumerClass();
        $consume = new ConsumeController([$fooConsumer, $barConsumer], $this->c['logger'], $this->c['access_checker']);

        $req = Request::create('/consume?jwt=' . UserHelper::ROOT_JWT, 'POST');
        $req->request->replace([
            'routingKey' => self::ROUTING_KEY,
            'body'       => $body,
            'context'    => $context,
        ]);
        $req->attributes->set('jwt.payload', Text::jwtContent(UserHelper::ROOT_JWT));
        $res = $consume->post($req);

        $this->assertEquals(204, $res->getStatusCode());
        $this->assertCount(1, $consumeCount);
        $this->assertArrayHasKey(self::ROUTING_KEY, $consumeCount);
        $this->assertEquals(2, $consumeCount[self::ROUTING_KEY]);
    }

    public function test500()
    {
        global $consumeCount;

        $fooConsumer = $this->consumerClass();
        $barConsumer = $this->consumerClass();
        $excConsumer = $this->consumerClass(true, (new Exception('foo')));
        $errConsumer = $this->consumerClass(true, (new Error('foo')));
        $consume = new ConsumeController(
            [$fooConsumer, $barConsumer, $excConsumer, $errConsumer],
            $this->c['logger'],
            $this->c['access_checker']
        );

        $req = Request::create('/consume?jwt=' . UserHelper::ROOT_JWT, 'POST');
        $req->request->replace(['routingKey' => self::ROUTING_KEY, 'body' => ['foo' => 'bar']]);
        $req->attributes->set('jwt.payload', Text::jwtContent(UserHelper::ROOT_JWT));
        $res = $consume->post($req);

        $this->assertEquals(500, $res->getStatusCode());
        $this->assertCount(1, $consumeCount);
        $this->assertArrayHasKey(self::ROUTING_KEY, $consumeCount);
        $this->assertEquals(2, $consumeCount[self::ROUTING_KEY]);
    }

    public function test204Empty()
    {
        $fooConsumer = $this->consumerClass(false);
        $consume = new ConsumeController([$fooConsumer], $this->c['logger'], $this->c['access_checker']);

        $req = Request::create('/consume?jwt=' . UserHelper::ROOT_JWT, 'POST');
        $req->attributes->set('jwt.payload', Text::jwtContent(UserHelper::ROOT_JWT));
        $req->request->replace(['routingKey' => self::ROUTING_KEY, 'body' => ['foo' => 'bar']]);
        $res = $consume->post($req);

        $this->assertEquals(204, $res->getStatusCode());
        global $consumeCount;
        $this->assertNull($consumeCount);
    }

    public function testLogWasteTime()
    {
        $fooConsumer = $this->consumerClass(false);
        $consume = new ConsumeController([$fooConsumer], $this->c['logger'], $this->c['access_checker'], true);

        $req = Request::create('/consume?jwt=' . UserHelper::ROOT_JWT, 'POST');
        $req->attributes->set('jwt.payload', Text::jwtContent(UserHelper::ROOT_JWT));
        $req->request->replace([
            'routingKey' => self::ROUTING_KEY,
            'body'       => ['foo' => 'bar'],
            'context'    => [MqClient::CONTEXT_TIMESTAMP => 1],
        ]);
        $res = $consume->post($req);
        foreach ($res->terminateCallbacks() as $callback) {
            call_user_func($callback);
        }
        $this->assertEquals(204, $res->getStatusCode());
        $wasteTime = time() - 1;
        $this->assertContains("consume.waste-time." . self::ROUTING_KEY . ": $wasteTime", $this->log['error'][0]);
    }
}
