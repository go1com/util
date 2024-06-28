<?php

namespace go1\clients;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use HTMLPurifier;

class RealtimeClient
{
    private HTMLPurifier $html;
    private Client       $client;
    private string       $realtimeUrl;

    public function __construct(Client $client, HTMLPurifier $html, string $realtimeUrl)
    {
        $this->client = $client;
        $this->html = $html;
        $this->realtimeUrl = $realtimeUrl;
    }

    public function notify(int $profileId = 0, array $data)
    {
        try {
            $this->client->post("{$this->realtimeUrl}/notification", [
                'headers' => ['Content-Type' => 'application/json'],
                'json'    => [
                    'pid'         => $profileId,
                    'message'     => $this->html->purify($data['message']),
                    'image'       => $data['image'] ?? null,
                    'tag'         => $data['tag'] ?? null,
                    'from'        => $data['from'] ?? null,
                    'instance_id' => $data['instance_id'] ?? null,
                    'event_type'  => $data['event_type'] ?? null,
                    'transient'   => $data['transient'] ?? null,
                ],
            ]);
        } catch (RequestException $e) {
        }
    }
}
