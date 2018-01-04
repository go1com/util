<?php

namespace go1\clients;

use go1\util\user\UserHelper;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\RequestException;
use Psr\Log\LoggerInterface;
use stdClass;

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

    public function create(stdClass $product, int $qty, string $paymentMethod, array $paymentOptions = [], string $authorization, array $metadata = [])
    {
        try {
            $res = $this->client->post("{$this->paymentUrl}/cart/process", [
                'headers' => ['Authorization' => $authorization, 'Content-Type' => 'application/json'],
                'json'    => $this->buildCartOptions($product, $qty, $paymentMethod, $paymentOptions, $metadata),
            ]);

            $transactionJson = $res->getBody()->getContents();
            if (!$transaction = json_decode($transactionJson)) {
                return false;
            }

            return $transaction;
        }
        catch (BadResponseException $e) {
            $this->logger->error("[#payment] Failed to transaction: " . $e->getMessage());

            return false;
        }
    }

    private function buildCartOptions(
        stdClass $product,
        int $qty,
        string $paymentMethod,
        array $paymentOptions = [],
        array $metadata = []
    ): array
    {
        $options = [
            'timestamp'      => time(),
            'paymentMethod'  => $paymentMethod,
            'paymentOptions' => $paymentOptions,
            'cartOptions'    => array_filter(['coupon' => $paymentOptions['coupon'] ?? null]),
            'metadata'       => $metadata,
        ];

        $options['cartOptions']['items'][] = [
            'instanceId'   => $product->instance_id,
            'productId'    => $product->id,
            'type'         => 'lo',
            'price'        => $product->pricing->price,
            'tax'          => isset($product->pricing->tax) ? $product->pricing->tax : 0.00,
            'tax_included' => isset($product->pricing->tax_included) ? $product->pricing->tax_included : true,
            'currency'     => $product->pricing->currency,
            'qty'          => $qty,
            'data'         => ['title' => $product->title],
        ];

        return $options;
    }

    public function updateCODTransaction($id): bool
    {
        try {
            $this->client->put("{$this->paymentUrl}/transaction/{$id}/complete");

            return true;
        }
        catch (BadResponseException $e) {
            $response = $e->getResponse();

            $this->logger->error(sprintf('[#payment] Failed to update transaction #%d: %s .', $id, $response->getBody()->getContents()));

            return false;
        }
    }
}
