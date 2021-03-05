<?php

namespace go1\clients;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use HTMLPurifier;

class RealtimeClient
{
    private HTMLPurifier $html;
    private Client $client;
    private string $realtimeUrl;

    public function __construct(Client $client, HTMLPurifier $html, string $realtimeUrl)
    {
        $this->client = $client;
        $this->html = $html;
        $this->realtimeUrl = $realtimeUrl;
    }

    public function notify(int $profileId, array $data, bool $retry = true)
    {
        try {
            $this->client->post("{$this->realtimeUrl}/notification",[
                'headers' => ['Content-Type' => 'application/json'],
                'json' => [
                    'pid'         => $profileId,
                    'message'     => $this->html->purify($data['message']),
                    'image'       => $data['image'] ?? null,
                    'tag'         => $data['tag'] ?? null,
                    'from'        => $data['from'] ?? null,
                    'instance_id' => $data['instance_id'] ?? null,
                    'event_type' => $data['event_type'] ?? null,
                ],
            ]);
        } catch (RequestException $e) {
            if ($retry) {
                $this->notify($profileId, $data, false);
            } else {
                throw $e;
            }
        }
    }
}
