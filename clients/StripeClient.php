<?php

namespace go1\clients;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Psr\Log\LoggerInterface;

class StripeClient
{
    private $client;
    private $logger;
    private $stripeToken;
    private $stripeEndpoint = 'https://api.stripe.com/v1/<method>';

    public function __construct(Client $client, LoggerInterface $logger, string $stripeToken)
    {
        $this->client = $client;
        $this->logger = $logger;
        $this->stripeToken = $stripeToken;
    }

    public function get(string $method, array $query = [], $timeout = 10)
    {
        return $this->makeRequest('get', $method, $query, $timeout);
    }

    public function post(string $method, array $query = [], $timeout = 10)
    {
        return $this->makeRequest('post', $method, $query, $timeout);
    }

    public function delete(string $method, array $query = [], $timeout = 10)
    {
        return $this->makeRequest('delete', $method, $query, $timeout);
    }

    private function makeRequest(string $httpVerb, string $method, array $query = [], $timeout = 10)
    {
        $url = str_replace('<method>', $method, $this->stripeEndpoint);

        try {
            return $this->client->request($httpVerb, $url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->stripeToken,
                    'Content-Type'  => 'application/x-www-form-urlencoded',
                ],
                'query'   => $query,
                'timeout' => $timeout,
            ]);
        }
        catch (RequestException $e) {
            $this->logger->error("Failed to $httpVerb Stripe. Reason: " . $e->getMessage());
        }
    }
}
