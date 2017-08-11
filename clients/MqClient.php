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

    const CONTEXT_ACTOR_ID = 'actor_id';
    const CONTEXT_TIMESTAMP = 'timestamp';
    const CONTEXT_DESCRIPTION = 'description';

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

    /**
     * Exchange message to process in sequence
     */
    public function publish($messageBody, string $routingKey, array $context = [])
    {
        $messageBody = is_scalar($messageBody) ? $messageBody : json_encode($messageBody);
        $message = new AMQPMessage($messageBody, [
            'content_type'        => 'application/json',
            'application_headers' => new AMQPTable($context),
        ]);
        $this->channel()->basic_publish($message, 'events', $routingKey);
    }

    /**
     *  Queue message to process in parallel.
     */
    public function queue($messageBody, string $routingKey, array $context = [])
    {
        $messageBody = is_scalar($messageBody) ? json_decode($messageBody) : $messageBody;
        $message = json_encode([
            'routingKey' => $routingKey,
            'body'       => $messageBody,
        ]);
        $message = new AMQPMessage($message, [
            'content_type'        => 'application/json',
            'application_headers' => new AMQPTable($context),
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
