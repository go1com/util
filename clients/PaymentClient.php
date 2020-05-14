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
        } catch (RequestException $e) {
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
        } catch (RequestException $e) {
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
        } catch (BadResponseException $e) {
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
        } catch (BadResponseException $e) {
            $response = $e->getResponse();

            $this->logger->error(sprintf('[#payment] Failed to update transaction #%d: %s .', $id, $response->getBody()->getContents()));

            return false;
        }
    }

    /**
     * Get Stripe customers by the supplied user_id
     * @param int $userId the user's id
     * @return mixed an array of customers from table 'payment_customer' if successful, false if unsuccessful.
     */
    public function getStripeCustomersByUserId(int $userId)
    {
        if (empty($userId)) {
            return [];
        }
        try {
            $connection = $this->client->get( "{$this->paymentUrl}/stripe/customer?user_id={$userId}&jwt=" . UserHelper::ROOT_JWT)
                ->getBody()
                ->getContents();
            return json_decode($connection);
        } catch (RequestException $e) {
            $this->logger->error("[#payment] Failed to fetch Stripe customers by user_id: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Create a payment customer from the supplied customer_id and user_id
     * @param string $customerId the Stripe customer_id
     * @param string $userId the GO1 user's id
     * @param array $payload the payment options such as stripe token, source, description, etc
     * @return mixed the ID of the new payment customer if successful, error if unsuccessful.
     */
    public function createPaymentCustomer(string $customerId, string $userId, array $payload)
    {
        if (empty($customerId) || empty($userId) || empty($payload)) {
            return "[#payment] Failed to create payment customer - invalid params";
        }
        $payload['customer_id'] = $customerId;
        $payload['user_id'] = $userId;
        try {
            $res = $this->client->post("{$this->paymentUrl}/stripe/customer?jwt=" . UserHelper::ROOT_JWT, [
                'headers' => ['Content-Type' => 'application/json'],
                'json'    => $payload
            ]);

            return $res->getBody()->getContents();
        } catch (RequestException $e) {
            $error = "[#payment] Failed to create payment customer by user_id: " . $e->getMessage();
            $this->logger->error($error);
            return $error;
        }
    }
}
