<?php

namespace go1\util\consume;


use go1\clients\MqClient;
use go1\util\contract\ConsumerInterface;
use stdClass;

abstract class NestableAbstractConsumer implements ConsumerInterface
{

    /** @var array ConsumerInterface[] */
    protected $consumers = [];
    protected $mqClient;

    public function __construct(array $consumers, MqClient $mqClient)
    {
        $this->consumers = $consumers;
        $this->mqClient = $mqClient;
    }


    public function consume(string $routingKey, stdClass $body, stdClass $context = null): bool
    {
        $taskBody = $body->body;
        if ($taskBody) {
            foreach ($this->consumers as $consumer) {
                if ($consumer->aware($body->task)) {
                    $isDone = $consumer->consume($body->task, $taskBody, $context);

                    if ($isDone && !empty($taskBody->doNext->routingKey) && !empty($taskBody->doNext->body)) {
                        // dispatch next round
                        $context = $context ? (array)$context : [];
                        $this->mqClient->publish($taskBody->doNext->body, $taskBody->doNext->routingKey, $context);
                    }
                }
            }

            return true;
        }

        return false;
    }
}

