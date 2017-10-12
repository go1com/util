<?php

namespace go1\clients;

use go1\util\queue\Queue;
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

    public function notify(int $profileId, array $data)
    {
        $this->queue->publish(
            [
                'method'  => 'POST',
                'url'     => "{$this->realtimeUrl}/notification",
                'query'   => '',
                'headers' => ['Content-Type' => 'application/json'],
                'body'    => json_encode([
                    'pid'         => $profileId,
                    'message'     => $this->html->purify($data['message']),
                    'image'       => $data['image'] ?? null,
                    'tag'         => $data['tag'] ?? null,
                    'from'        => $data['from'] ?? null,
                    'instance_id' => $data['instance_id'] ?? null,
                ]),
            ],
            Queue::DO_CONSUMER_HTTP_REQUEST
        );
    }
}
