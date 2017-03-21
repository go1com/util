<?php

namespace go1\clients;

use GuzzleHttp\Client;

class EntityClient
{
    private $client;
    private $entityUrl;

    public function __construct(Client $client, string $entityUrl)
    {
        $this->client = $client;
        $this->entityUrl = $entityUrl;
    }

    public function bump($type, $bundle)
    {
        $url = "{$this->entityUrl}/bump/{$type}/{$bundle}";
        $res = $this->client->post($url)->getBody()->getContents();

        return (($res = json_decode($res)) && !empty($res->id)) ? $res->id : false;
    }
}
