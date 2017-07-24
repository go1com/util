<?php

namespace go1\util\tests\plan;

use go1\util\payment\PaymentStripeHelper;
use go1\util\tests\UtilTestCase;

class PaymentStripeHelperTest extends UtilTestCase
{
    public function testStripeSupportedCurrencies()
    {
        $currencies = PaymentStripeHelper::currencies();

        $this->assertTrue(isset($currencies['JPY']));
        $this->assertEquals('Japanese Yen', $currencies['JPY']['name']);
        $this->assertEquals(0, $currencies['JPY']['decimals']);
        $this->assertEquals(50.00, $currencies['JPY']['min']);

        $this->assertTrue(isset($currencies['USD']));
        $this->assertEquals('United States Dollar', $currencies['USD']['name']);
        $this->assertEquals(2, $currencies['USD']['decimals']);
        $this->assertEquals(0.5, $currencies['USD']['min']);
    }
}
