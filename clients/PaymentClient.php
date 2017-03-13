<?php

namespace go1\clients;

use go1\util\user\UserHelper;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Psr\Log\LoggerInterface;

class PaymentClient
{
    private $logger;
    private $client;
    private $paymentUrl;

    public function __construct(LoggerInterface $logger, Client $client, string $paymentUrl)
    {
        $this->logger = $logger;
        $this->client = $client;
        $this->paymentUrl = rtrim($paymentUrl, '/');
    }

    public function stripeConnectionId(string $instance)
    {
        try {
            $connection = $this
                ->client
                ->get("{$this->paymentUrl}/stripe/info/{$instance}/id?jwt=" . UserHelper::ROOT_JWT)
                ->getBody()
                ->getContents();

            return json_decode($connection)->id;
        }
        catch (RequestException $e) {
            $this->logger->error("[#payment] Failed to fetch portal Stripe connection ID: " . $e->getMessage());

            return false;
        }
    }

    public function stripeConnection(string $instance)
    {
        try {
            $connection = $this
                ->client
                ->get("{$this->paymentUrl}/stripe/info/{$instance}/read_write?jwt=" . UserHelper::ROOT_JWT)
                ->getBody()
                ->getContents();

            return json_decode($connection);
        }
        catch (RequestException $e) {
            $this->logger->error("[#payment] Failed to fetch portal Stripe connection: " . $e->getMessage());

            return false;
        }
    }
}
