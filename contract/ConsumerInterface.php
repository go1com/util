<?php

namespace go1\util\contract;

use stdClass;

interface ConsumerInterface
{
    public function aware(string $event): bool;

    public function consume(string $routingKey, stdClass $body, stdClass $context = null): bool;
}
