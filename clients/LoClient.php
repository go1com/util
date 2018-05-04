<?php

namespace go1\clients;

use Exception;
use go1\util\queue\Queue;
use go1\util\user\UserHelper;
use GuzzleHttp\Client;

class LoClient
{
    private $client;
    private $loUrl;
    private $queue;

    public function __construct(Client $client, string $loUrl, MqClient $queue)
    {
        $this->client = $client;
        $this->loUrl = $loUrl;
        $this->queue = $queue;
    }

    public function load($id)
    {
        $jwt = UserHelper::ROOT_JWT;
        $res = $this->client->get("$this->loUrl/admin/lo/{$id}?jwt=$jwt", ['http_errors' => false]);
        if (200 == $res->getStatusCode()) {
            return json_decode($res->getBody()->getContents());
        }

        return false;
    }

    public function eventCapability(int $eventId): array
    {
        $res = $this->client->get("{$this->loUrl}/event/{$eventId}/available-seat", ['http_errors' => false]);
        if (200 != $res->getStatusCode()) {
            return [];
        }

        return json_decode($res->getBody(), true);
    }

    public function eventAvailableSeat(int $eventId)
    {
        $capability = $this->eventCapability($eventId);

        return $capability['count'] ?? false;
    }

    public function share(int $portalId, int $loId, bool $negative = false)
    {
        if (!($this->queue instanceof MqClient)) {
            throw new Exception('Missing queue configurations.');
        }

        $this->queue->publish(
            [
                'method'  => $negative ? 'DELETE' : 'POST',
                'url'     => "{$this->loUrl}/lo/{$loId}/share/{$portalId}",
                'query'   => '',
                'headers' => [
                    'Content-Type'  => 'application/json',
                    'Authorization' => 'Bearer ' . UserHelper::ROOT_JWT
                ]
            ],
            Queue::DO_CONSUMER_HTTP_REQUEST
        );
    }
}
