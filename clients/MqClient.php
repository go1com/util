<?php

namespace go1\clients;

use Exception;
use go1\util\AccessChecker;
use go1\util\queue\MqDefaultHandler;
use go1\util\queue\QueueMiddlewareInterface;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use Pimple\Container;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\HttpFoundation\Request;

class MqClient
{
    /** @var AMQPChannel */
    private $channel;
    private $host;
    private $port;
    private $user;
    private $pass;
    private $logger;
    private $accessChecker;
    private $container;
    private $request;

    const CONTEXT_ACTOR_ID    = 'actor_id';
    const CONTEXT_TIMESTAMP   = 'timestamp';
    const CONTEXT_DESCRIPTION = 'description';
    const CONTEXT_REQUEST_ID  = 'request_id';
    const CONTEXT_INTERNAL    = 'internal';

    /** @var []QueueMiddlewareInterface */
    private $middlewares = [];

    public function __construct(
        $host, $port, $user, $pass,
        LoggerInterface $logger = null,
        AccessChecker $accessChecker = null,
        Container $container = null,
        Request $request = null
    )
    {
        $this->host = $host;
        $this->port = $port;
        $this->user = $user;
        $this->pass = $pass;
        $this->logger = $logger ?: new NullLogger;
        $this->accessChecker = $accessChecker;
        $this->container = $container;
        $this->request = $request;
    }

    public function addMiddleware(QueueMiddlewareInterface $handler, string $name = null)
    {
        if ($name) {
            $this->middlewares[$name] = $handler;
        }
        else {
            $this->middlewares[] = $handler;
        }
    }

    /**
     * @return []QueueMiddlewareInterface
     */
    public function middlewares(): array
    {
        if (!isset($this->middlewares['default'])) {
            $this->addMiddleware(new MqDefaultHandler($this->channel()), 'default');
        }

        return $this->middlewares;
    }

    private function channel()
    {
        if (null === $this->channel) {
            $connection = new AMQPStreamConnection($this->host, $this->port, $this->user, $this->pass);
            $this->channel = $connection->channel();
            $this->channel->exchange_declare('events', 'topic', false, false, false);
        }

        return $this->channel;
    }

    public function close()
    {
        $this->channel()->close();
    }

    public function publish(array $body, string $routingKey, array $context = [], $exchange = 'events')
    {
        $this->queue($body, $routingKey, $context, $exchange);
    }

    private function currentRequest()
    {
        if ($this->request) {
            return $this->request;
        }

        return ($this->container && $this->container->offsetExists('request_stack'))
            ? $this->container['request_stack']->getCurrentRequest()
            : null;
    }

    public function queue(array $body, string $routingKey, array $context = [], $exchange = '')
    {
        $req = $this->currentRequest();
        $req && self::parseRequestContext($req, $context, $this->accessChecker);

        /** @var QueueMiddlewareInterface $middleware */
        foreach ($this->middlewares() as $middleware) {
            if (!$middleware->handle($exchange, $routingKey, $body, $context, $req)) {
                break;
            }
        }

        $this->logger->debug($body, ['exchange' => $exchange, 'routingKey' => $routingKey, 'context' => $context]);
    }

    public function subscribe($bindingKey = '#', callable $callback)
    {
        $channel = $this->channel();
        $channel->exchange_declare($exchange = 'events', 'topic', false, false, false);
        $queue = $channel->queue_declare('', false, false, true, false)[0];
        $channel->queue_bind($queue, $exchange, $bindingKey);
        $channel->basic_consume($queue, '', $noLocal = false, $noAck = false, $exclusive = false, $nowait = false, function ($msg) use ($channel, $callback) {
            $callback($channel, $msg);
        });

        while (count($channel->callbacks)) {
            try {
                $channel->wait();
            }
            catch (Exception $e) {
                $channel->close();
            }
        }
    }

    public static function parseRequestContext(Request $request, array &$context = [], AccessChecker $accessChecker = null)
    {
        if (!isset($context[self::CONTEXT_REQUEST_ID])) {
            if ($requestId = $request->headers->get('X-Request-Id')) {
                $context[self::CONTEXT_REQUEST_ID] = $requestId;
            }
        }

        $accessChecker = $accessChecker ?: new AccessChecker;
        if (!isset($context[self::CONTEXT_ACTOR_ID]) && $accessChecker) {
            $user = $accessChecker->validUser($request);
            $user && $context[self::CONTEXT_ACTOR_ID] = $user->id;
        }
    }
}
