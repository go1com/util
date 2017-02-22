<?php

namespace go1\clients;

use go1\util\Queue;
use HTMLPurifier;

class RealtimeClient
{
    private $queue;
    private $html;
    private $realtimeUrl;

    public function __construct(MqClient $queue, HTMLPurifier $html, string $realtimeUrl)
    {
        $this->queue = $queue;
        $this->html = $html;
        $this->realtimeUrl = $realtimeUrl;
    }

    public function notify(int $instanceId, int $profileId, string $body)
    {
        $this->queue->publish(
            [
                'method'  => 'POST',
                'url'     => "{$this->realtimeUrl}/notification",
                'query'   => '',
                'headers' => ['Content-Type' => 'application/json'],
                'body'    => json_encode([
                    'instance_id' => $instanceId,
                    'pid'         => $profileId,
                    'message'     => $this->html->purify($body),
                ]),
            ],
            Queue::DO_CONSUMER_HTTP_REQUEST
        );
    }
}
