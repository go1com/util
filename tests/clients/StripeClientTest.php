<?php

namespace go1\util\tests\clients;

use go1\clients\StripeClient;
use go1\util\tests\UtilTestCase;
use GuzzleHttp\Client;

class StripeClientTest extends UtilTestCase
{
    private $stripeToken = 'sk_test_BQokikJOvBiI2HlWgH4olfQ2';

    public function testGet()
    {
        $client = new Client();
        $c = $this->getContainer();
        $stripe = new StripeClient($client, $c['logger'], $this->stripeToken);
        $response = $stripe->get('customers', ['limit' => 10, 'offset' => 0]);
        $customers = json_decode($response->getBody()->getContents())->data;

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertCount(10, $customers);
    }

    public function testPost()
    {
        $client = new Client();
        $c = $this->getContainer();
        $stripe = new StripeClient($client, $c['logger'], $this->stripeToken);
        $response = $stripe->post('customers', ['description' => 'AT', 'source' => 'tok_mastercard']);
        $customer = json_decode($response->getBody()->getContents());

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('customer', $customer->object);

        return $customer->id;
    }

    /** @depends testPost */
    public function testDelete(string $customerId)
    {
        $client = new Client();
        $c = $this->getContainer();
        $stripe = new StripeClient($client, $c['logger'], $this->stripeToken);
        $response = $stripe->delete("customers/$customerId");
        $customer = json_decode($response->getBody()->getContents());

        $this->assertEquals($customerId, $customer->id);
        $this->assertEquals('customer', $customer->object);
        $this->assertTrue($customer->deleted);
    }
}
