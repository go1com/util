<?php

namespace go1\clients;

use Exception;
use go1\util\Queue;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class MqClient
{
    /** @var AMQPChannel */
    private $channel;
    private $host;
    private $port;
    private $user;
    private $pass;
    private $logger;

    const CONTEXT_ACTOR_ID    = 'actor_id';
    const CONTEXT_TIMESTAMP   = 'timestamp';
    const CONTEXT_DESCRIPTION = 'description';

    public function __construct($host, $port, $user, $pass, LoggerInterface $logger = null)
    {
        $this->host = $host;
        $this->port = $port;
        $this->user = $user;
        $this->pass = $pass;
        $this->logger = $logger ?: new NullLogger;
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

    public function publish($body, string $routingKey, array $context = [])
    {
        $this->queue($body, $routingKey, $context, 'events');
    }

    public function queue($body, string $routingKey, array $context = [], $exchange = '')
    {
        $body = is_scalar($body) ? json_decode($body) : $body;
        $this->processMessage($body, $routingKey);

        if ($service = getenv('SERVICE_80_NAME')) {
            $context['app'] = $service;
        }

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
        if (strpos($routingKey, '.update')) {
            if (
                (is_array($body) && !(array_key_exists('id', $body) && $body['id']))
                || (is_object($body) && !(property_exists($body, 'id') && $body->id))
            ) {
                throw new Exception("Missing entity ID.");
            }
        }
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
}
