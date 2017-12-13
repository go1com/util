<?php

namespace go1\clients;

use GuzzleHttp\Client;

class QueueClient
{
    private $client;
    private $queueUrl;

    public function __construct(Client $client, string $queueUrl)
    {
        $this->client = $client;
        $this->queueUrl = rtrim($queueUrl, '/');
    }

    /**
     * @TODO Support tags.
     *
     * @param  string $queueName
     * @param string  $callback
     * @param array   $arguments
     * @param int     $priority
     */
    public function queue($queueName, $callback, array $arguments = [], $priority = 0)
    {
        $this->client->post("{$this->queueUrl}/", [
            'headers' => ['Content-Type' => 'application/json'],
            'json'    => [
                'queueName' => $queueName,
                'callback'  => $callback,
                'arguments' => $arguments,
                'priority'  => $priority,
            ]]);
    }

    public function queueMultiple(array $messages = [])
    {
        $this->client->post("{$this->queueUrl}/", [
            'headers' => ['Content-Type' => 'application/json'],
            'json'    => [$messages],
        ]);
    }
}
