<?php

namespace go1\clients;

use Exception;
use go1\util\Queue;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;

class MqClient
{
    /** @var AMQPChannel */
    private $channel;
    private $host;
    private $port;
    private $user;
    private $pass;
    public static $priority = self::PRIORITY_NORMAL;

    const CONTEXT_ACTOR_ID = 'actor_id';
    const CONTEXT_TIMESTAMP = 'timestamp';
    const CONTEXT_DESCRIPTION = 'description';

    const PRIORITY_BLOCKER  = 9;
    const PRIORITY_CRITICAL = 8;
    const PRIORITY_HIGH     = 7;
    const PRIORITY_NORMAL   = 4;
    const PRIORITY_LOW      = 2;
    const PRIORITY_TRIVIAL  = 0;
    const PRIORITY_DISABLED = -1;
    const PRIORITIES = [
        self::PRIORITY_BLOCKER,
        self::PRIORITY_CRITICAL,
        self::PRIORITY_HIGH,
        self::PRIORITY_NORMAL,
        self::PRIORITY_LOW,
        self::PRIORITY_TRIVIAL,
        self::PRIORITY_DISABLED,
    ];

    public function __construct($host, $port, $user, $pass)
    {
        $this->host = $host;
        $this->port = $port;
        $this->user = $user;
        $this->pass = $pass;
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

    public function priority(int $priority = null) {
        if (null === $priority) {
            return self::$priority;
        }

        return self::$priority = in_array($priority, self::PRIORITIES) ? $priority : self::PRIORITY_NORMAL;
    }

    /**
     * Exchange message to process in sequence
     */
    public function publish($messageBody, string $routingKey, array $context = [], int $priority = null)
    {
        $messageBody = is_scalar($messageBody) ? $messageBody : json_encode($messageBody);
        if ($serviceName = getenv('SERVICE_80_NAME')) {
            $context['app'] = $serviceName;
        }
        $message = new AMQPMessage($messageBody, [
            'content_type'        => 'application/json',
            'application_headers' => new AMQPTable($context),
            'priority'            => $this->priority($priority),
        ]);
        $this->channel()->basic_publish($message, 'events', $routingKey);
    }

    /**
     *  Queue message to process in parallel.
     */
    public function queue($messageBody, string $routingKey, array $context = [], int $priority = null)
    {
        $messageBody = is_scalar($messageBody) ? json_decode($messageBody) : $messageBody;
        $message = json_encode([
            'routingKey' => $routingKey,
            'body'       => $messageBody,
        ]);
        if ($serviceName = getenv('SERVICE_80_NAME')) {
            $context['app'] = $serviceName;
        }
        $message = new AMQPMessage($message, [
            'content_type'        => 'application/json',
            'application_headers' => new AMQPTable($context),
            'priority'            => $this->priority($priority),
        ]);
        $this->channel()->basic_publish($message, '', Queue::WORKER_QUEUE_NAME);
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
