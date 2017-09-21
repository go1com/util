<?php

namespace go1\clients;

use go1\util\queue\Queue;
use GuzzleHttp\Client;

class GraphinClient
{
    private $queue;
    private $client;
    private $graphinUrl;

    public function __construct(Client $client, string $graphinUrl, MqClient $queue)
    {
        $this->client = $client;
        $this->queue = $queue;
        $this->graphinUrl = rtrim($graphinUrl, '/');
    }

    public function query($query, array $context = [])
    {
        return $this->client->post(
            $this->graphinUrl . '/',
            [
                'headers' => ['Content-Type' => 'application/json'],
                'json'    => ['query' => $query, 'context' => $context],
            ]
        );
    }

    public function stackQuery(array $stack)
    {
        return $this->client->post(
            $this->graphinUrl . '/',
            [
                'headers' => ['Content-Type' => 'application/json'],
                'json'    => ['stack' => $stack],
            ]
        );
    }

    public function importPortal($id)
    {
        $this->import('portal', $id);
    }

    public function importUser($id)
    {
        $this->import('user', $id);
    }

    public function importLearningObject($id)
    {
        $this->import('lo', $id);
    }

    public function importVote($id)
    {
        $this->import('vote', $id);
    }

    private function import(string $type, int $id)
    {
        $this->queue->publish(['type' => $type, 'id' => $id], Queue::DO_GRAPHIN_IMPORT);
    }
}
