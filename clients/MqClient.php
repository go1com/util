<?php

namespace go1\clients;

use Exception;
use go1\util\AccessChecker;
use go1\util\queue\Queue;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;
use Pimple\Container;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PropertyAccess\PropertyAccess;

class MqClient
{
    /** @var AMQPChannel[] */
    private $channels;
    private $host;
    private $port;
    private $user;
    private $pass;
    private $logger;
    private $accessChecker;
    private $container;
    private $request;
    private $propertyAccessor;

    const CONTEXT_ACTOR_ID    = 'actor_id';
    const CONTEXT_ACTION      = 'action';
    const CONTEXT_DESCRIPTION = 'description';
    /*** @deprecated */
    const CONTEXT_PORTAL      = 'instance';
    const CONTEXT_INTERNAL    = 'internal';
    const CONTEXT_REQUEST_ID  = 'request_id';
    const CONTEXT_TIMESTAMP   = 'timestamp';

    # For message splitting
    const CONTEXT_ENTITY_TYPE = 'entity-type';
    const CONTEXT_PORTAL_NAME = 'portal-name';

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
        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
    }

    public function channel($exchange = 'events', $type = 'topic'): AMQPChannel
    {
        if (!isset($this->channels[$exchange][$type])) {
            $connection = new AMQPStreamConnection($this->host, $this->port, $this->user, $this->pass);
            $channel = $connection->channel();
            $channel->exchange_declare($exchange, $type, false, false, false);
            $this->channels[$exchange][$type] = $channel;
        }

        return $this->channels[$exchange][$type];
    }

    public function close()
    {
        $this->channel()->close();
    }

    public function publish($body, string $routingKey, array $context = [])
    {
        $this->queue($body, $routingKey, $context, 'events');
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

    public function queue($body, string $routingKey, array $context = [], $exchange = '')
    {
        $body = is_scalar($body) ? json_decode($body) : $body;
        $this->processMessage($body, $routingKey);

        if ($request = $this->currentRequest()) {
            self::parseRequestContext($request, $context, $this->accessChecker);
        }

        if ($service = getenv('SERVICE_80_NAME')) {
            $context['app'] = $service;
        }
        $context[static::CONTEXT_TIMESTAMP] = $context[static::CONTEXT_TIMESTAMP] ?? time();

        if (!$exchange) {
            $body = json_encode(['routingKey' => $routingKey, 'body' => $body]);
            $routingKey = Queue::WORKER_QUEUE_NAME;
        }

        $this->channel()->basic_publish(
            new AMQPMessage($body = is_scalar($body) ? $body : json_encode($body), ['content_type' => 'application/json', 'application_headers' => new AMQPTable($context)]),
            $exchange,
            $routingKey
        );

        $this->logger->debug($body, ['exchange' => $exchange, 'routingKey' => $routingKey, 'context' => $context]);
    }

    private function processMessage($body, string $routingKey)
    {
        # Quiz does not have `id` property.
        if (Queue::QUIZ_USER_ANSWER_UPDATE == $routingKey) {
            return null;
        }

        $explode = explode('.', $routingKey);
        $isLazy = isset($explode[0]) && ('do' == $explode[0]); # Lazy = do.SERVICE.#

        if (strpos($routingKey, '.update') && !$isLazy) {
            if ('post_' === substr($routingKey, 0, 5)) {
                return null;
            }

            if (
                (
                    is_array($body)
                    && !(2 === count(array_filter($body, function ($value, $key) {
                            return (in_array($key, ['id', 'original']) && $value);
                        }, ARRAY_FILTER_USE_BOTH)))
                )
                ||
                (
                    is_object($body)
                    && (!(property_exists($body, 'id') && $this->propertyAccessor->getValue($body, 'id'))
                        || !(property_exists($body, 'original') && $this->propertyAccessor->getValue($body, 'original')))
                )
            ) {
                throw new Exception("Missing entity ID or original data.");
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
