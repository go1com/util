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
        $c['payment.options'] = [
            'api_key'    => 'API_KEY',
            'secret_key' => 'SECRECT_KEY'
        ];
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
                    $this->assertEquals("APP_ID", $options['json']['applicationId']);

                    $item = $options['json']['cartOptions']['items'][0];
                    $this->assertEquals("lo-100", $item['productId']);
                    $this->assertEquals("product", $item['type']);
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
        $c['payment.options'] = [
            'api_key'    => 'API_KEY',
            'secret_key' => 'SECRECT_KEY'
        ];

        /** @var PaymentClient $paymentClient */
        $paymentClient = $c['go1.client.payment'];
        $product = (object) [
            'id'        => 100,
            'title'     => 'test product',
            'pricing'   => (object) [
                'price'         => 1000,
                'tax'           => 10,
                'tax_included'  => true,
                'currency'      => 'AUD'
            ]
        ];
        $paymentClient->setAppId('APP_ID');
        $paymentClient->setAppSecret('SECRET_KEY');
        $paymentClient->create($product, 10, 'cod', [], 'USER_JWT');
    }
}
