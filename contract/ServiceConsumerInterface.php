<?php

namespace go1\util\contract;

use stdClass;

interface ServiceConsumerInterface
{
    /**
     * @return string
     */
    public function name(): string;

    /**
     * @return [string]string — routingKey -> description.
     */
    public function aware(): array;

    /**
     * Consume the message.
     *
     * @param string        $routingKey
     * @param stdClass      $body
     * @param stdClass|null $context
     */
    public function consume(string $routingKey, stdClass $body, stdClass $context = null);
}
