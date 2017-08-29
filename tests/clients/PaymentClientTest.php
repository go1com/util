<?php

namespace go1\util\schema\tests;

use go1\clients\PaymentClient;
use go1\util\tests\UtilTestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;

class PaymentClientTest extends UtilTestCase
{
    private $paymentUrl = 'http://payment.dev.go1.service';

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
}
