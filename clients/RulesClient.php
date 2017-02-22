<?php

namespace go1\clients;

use Firebase\JWT\JWT;
use GuzzleHttp\Client;

class RulesClient
{
    use RequestTrait;

    private $rulesUrl;

    public function __construct(Client $client, $rulesUrl, QueueClient $queueClient)
    {
        $this->rulesUrl = rtrim($rulesUrl, '/');
        $this->setClient($client);
        $this->setQueueClient($queueClient);
    }

    public function invoke($event, array $payload)
    {
        $url = "{$this->rulesUrl}/invoke/{$event}";
        $jwt = JWT::encode(['object' => ['type' => 'user', 'content' => ['mail' => 'admin@accounts.gocatalyze.com', 'roles' => ['Admin on #Accounts']]]], 'GO1_INTERNAL');

        $this->request(
            'POST',
            $url,
            ['Content-Type' => 'application/json', 'Authorization' => "Bearer $jwt"],
            ['payload' => $payload]
        );
    }
}
