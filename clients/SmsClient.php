<?php

namespace go1\clients;

use go1\util\queue\Queue;
use GuzzleHttp\Client;

class SmsClient
{
    use RequestTrait;

    private $smsUrl;
    private $mqClient;

    public function __construct(Client $client, string $url, QueueClient $queueClient, MqClient $mqClient)
    {
        $this->smsUrl = $url;
        $this->mqClient = $mqClient;

        $this->setClient($client);
        $this->setQueueClient($queueClient);
    }

    public function send(string $to, $body)
    {
        $this->mqClient->publish(['to' => $to, 'body' => $body], Queue::DO_SMS_SEND);
    }

    public function verify(string $phoneNumber)
    {
        return $this->client->get("{$this->smsUrl}/verify/{$phoneNumber}");
    }
}
