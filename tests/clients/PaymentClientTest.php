<?php

namespace go1\util\schema\tests;

use go1\clients\PaymentClient;
use go1\util\tests\UtilTestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;

class PaymentClientTest extends UtilTestCase
{
    private $paymentUrl = 'http://payment.dev.go1.service';

    /**
     * @runInSeparateProcess
     */
    public function testUpdateCODTransaction()
    {
        $c = $this->getContainer();

        $client = $this->getMockBuilder(Client::class)->setMethods(['put'])->getMock();
        $client
            ->expects($this->once())
            ->method('put')
            ->with($this->callback(function ($url) {
                $this->assertEquals("{$this->paymentUrl}/transaction/1000/complete", $url);

                return true;
            }))
            ->willReturn(new Response());

        $c['client'] = $client;
        $c['payment_url'] = $this->paymentUrl;

        /** @var PaymentClient $paymentClient */
        $paymentClient = $c['go1.client.payment'];
        $paymentClient->updateCODTransaction(1000);
    }

    /**
     * @runInSeparateProcess
     */
    public function testCreate()
    {
        $c = $this->getContainer();

        $client = $this->getMockBuilder(Client::class)->setMethods(['post'])->getMock();
        $client
            ->expects($this->once())
            ->method('post')
            ->with($this->callback(function ($url) {
                $this->assertEquals("{$this->paymentUrl}/cart/process", $url);

                return true;
            }),
                $this->callback(function ($options) {
                    $this->assertEquals("USER_JWT", $options['headers']['Authorization']);
                    $this->assertEquals("application/json", $options['headers']['Content-Type']);

                    $item = $options['json']['cartOptions']['items'][0];
                    $this->assertEquals(100, $item['productId']);
                    $this->assertEquals("lo", $item['type']);
                    $this->assertEquals(1000, $item['price']);
                    $this->assertEquals(10, $item['tax']);
                    $this->assertTrue($item['tax_included']);
                    $this->assertEquals('AUD', $item['currency']);
                    $this->assertEquals(10, $item['qty']);
                    $this->assertEquals("test product", $item['data']['title']);
                    $this->assertEquals("cod", $options['json']['paymentMethod']);

                    return true;
                })
            )
            ->willReturn(new Response());

        $c['client'] = $client;
        $c['payment_url'] = $this->paymentUrl;

        /** @var PaymentClient $paymentClient */
        $paymentClient = $c['go1.client.payment'];
        $product = (object) [
            'id'          => 100,
            'title'       => 'test product',
            'instance_id' => 555,
            'pricing'     => (object) [
                'price'        => 1000,
                'tax'          => 10,
                'tax_included' => true,
                'currency'     => 'AUD',
            ],
        ];
        $paymentClient->create($product, 10, 'cod', [], 'USER_JWT');
    }

    /**
     * @runInSeparateProcess
     */
    public function testGetStripeCustomersByUserId()
    {
        $userId = 111415;
        $mockPaymentCustomers = [
            [
                "id"          => 9,
                "user_id"     => $userId,
                "customer_id" => "cus_9rxaedJs1",
                "token"       => "tok_manual",
                "status"      => "1",
                "description" => "here be description",
                "metadata"    => [
                    "source"    => "payment_details"
                ],
                "created"     => "1483532066",
                "updated"     => "1483532066"
            ]
        ];

        $client = $this->getMockBuilder(Client::class)->setMethods(['get'])->getMock();
        $client
            ->expects($this->once())
            ->method('get')
            ->with($this->callback(function ($url) use ($userId) {
                $this->assertStringContainsString("{$this->paymentUrl}/stripe/customer?user_id=$userId", $url);
                return true;
            }))
            ->willReturn(new Response(200, [], json_encode($mockPaymentCustomers)));

        $c = $this->getContainer();
        $c['client'] = $client;
        $c['payment_url'] = $this->paymentUrl;
        /** @var PaymentClient $paymentClient */
        $paymentClient = $c['go1.client.payment'];

        $res = $paymentClient->getStripeCustomersByUserId($userId);

        $this->assertTrue(count($res) > 0);
        $actual = $res[0];
        $expected = $mockPaymentCustomers[0];

        $this->assertEquals($actual->id, $expected['id']);
        $this->assertEquals($actual->user_id, $expected['user_id']);
        $this->assertEquals($actual->customer_id, $expected['customer_id']);
        $this->assertEquals($actual->token, $expected['token']);
        $this->assertEquals($actual->status, $expected['status']);
        $this->assertEquals($actual->description, $expected['description']);
    }

    /**
     * @runInSeparateProcess
     */
    public function testCreatePaymentCustomer()
    {
        $payload = [
            'token'       => 'tok_visa',
            'source'      => 'Content-subscription',
            'description' => 'Individual-purchase-level',
            'customer_id' => 'cus_abcnco121j',
            'user_id'     => 115708
        ];
        $mockCustomerIdResult = [
            'id' => 188999
        ];

        $client = $this->getMockBuilder(Client::class)->setMethods(['post'])->getMock();
        $client
            ->expects($this->once())
            ->method('post')
            ->with($this->callback(function ($url) {
                $this->assertStringContainsString("{$this->paymentUrl}/stripe/customer", $url);
                return true;
            }), $this->callback(function ($options) use ($payload) {
                $this->assertEquals("application/json", $options['headers']['Content-Type']);
                $actual = $options['json'];
                $this->assertEquals($actual, $payload);
                return true;
            }))
            ->willReturn(new Response(200, [], json_encode($mockCustomerIdResult)));

        $c = $this->getContainer();
        $c['client'] = $client;
        $c['payment_url'] = $this->paymentUrl;
        /** @var PaymentClient $paymentClient */
        $paymentClient = $c['go1.client.payment'];

        $res = $paymentClient->createPaymentCustomer($payload);
        $this->assertEquals($res->id, $mockCustomerIdResult['id']);
    }
}
