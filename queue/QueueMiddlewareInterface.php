<?php

namespace go1\util\queue;

use Symfony\Component\HttpFoundation\Request;

interface QueueMiddlewareInterface
{
    /**
     * @param string  $exchange
     * @param string  $routingKey
     * @param array   $body
     * @param array   $context
     * @param Request $req
     * @return bool Return false if don't call next.
     */
    public function handle(
        string $exchange,
        string $routingKey,
        array &$body,
        array &$context = [],
        Request $req = null): bool;
}
