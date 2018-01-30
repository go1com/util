<?php

namespace go1\clients;

use go1\util\user\UserHelper;
use GuzzleHttp\Client;

class LoClient
{
    private $client;
    private $loUrl;

    public function __construct(Client $client, string $loUrl)
    {
        $this->client = $client;
        $this->loUrl = $loUrl;
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

    public function eventAvailableSeat(int $eventId)
    {
        $res = $this->client->get("{$this->loUrl}/event/{$eventId}/available-seat", ['http_errors' => false]);
        if (200 != $res->getStatusCode()) {
            return false;
        }

        return json_decode($res->getBody())->count;
    }

    public function shareLo(int $instanceId, int $loId)
    {
        $url = "{$this->loUrl}/lo/{$loId}/share/instance/{$instanceId}?jwt=" . UserHelper::ROOT_JWT;
        $this->client->post($url);
    }
}
