<?php

namespace go1\util\consume;

use go1\util\contract\ConsumerInterface;
use stdClass;

/** For testing purpose only */
class Consumer implements ConsumerInterface
{
    public function aware(string $event): bool
    {
        return true;
    }

    public function consume(string $routingKey, stdClass $body): bool
    {
        return true;
    }

}