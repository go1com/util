<?php

namespace go1\util\tests\portal;

use go1\util\portal\PortalHelper;
use go1\util\portal\PortalPricing;
use go1\util\schema\mock\InstanceMockTrait;
use go1\util\tests\UtilTestCase;

class PortalPricingTest extends UtilTestCase
{
    use InstanceMockTrait;

    public function testPricePlatformFree()
    {
        $data = [
            'user_plan' => [
                'license' => 5
            ]
        ];
        $instanceId = $this->createInstance($this->db, ['data' => $data]);

        $portal = PortalHelper::load($this->db, $instanceId);
        list($price, $currency) = PortalPricing::getPrice($portal);

        $this->assertEquals(0, $price);
        $this->assertEquals('AUD', $currency);
    }

    public function testPricePlatform10LicenseAUUS()
    {
        $data = [
            'user_plan' => [
                'license' => 10
            ]
        ];
        $instanceId = $this->createInstance($this->db, ['data' => $data]);

        $portal = PortalHelper::load($this->db, $instanceId);
        list($price, $currency) = PortalPricing::getPrice($portal);

        $this->assertEquals(600, $price);
        $this->assertEquals('AUD', $currency);
    }

    public function testPricePlatform10LicenseEU()
    {
        $data = [
            'user_plan' => [
                'license'   => 10,
                'regional'  => 'EU'
            ]
        ];
        $instanceId = $this->createInstance($this->db, ['data' => $data]);

        $portal = PortalHelper::load($this->db, $instanceId);
        list($price, $currency) = PortalPricing::getPrice($portal);

        $this->assertEquals(540, $price);
        $this->assertEquals('EUR', $currency);
    }

    public function testPricePlatform10LicenseUK()
    {
        $data = [
            'user_plan' => [
                'license'   => 10,
                'regional'  => 'UK'
            ]
        ];
        $instanceId = $this->createInstance($this->db, ['data' => $data]);

        $portal = PortalHelper::load($this->db, $instanceId);
        list($price, $currency) = PortalPricing::getPrice($portal);

        $this->assertEquals(480, $price);
        $this->assertEquals('GBP', $currency);
    }

    public function testPricePremium10LicenseAUUS()
    {
        $data = [
            'user_plan' => [
                'license'   => 10,
                'regional'  => 'AU',
                'product'   => 'premium'
            ]
        ];
        $instanceId = $this->createInstance($this->db, ['data' => $data]);

        $portal = PortalHelper::load($this->db, $instanceId);
        list($price, $currency) = PortalPricing::getPrice($portal);

        $this->assertEquals(1080, $price);
        $this->assertEquals('AUD', $currency);
    }

    public function testPricePremium10LicenseEU()
    {
        $data = [
            'user_plan' => [
                'license'   => 10,
                'regional'  => 'EU',
                'product'   => 'premium'
            ]
        ];
        $instanceId = $this->createInstance($this->db, ['data' => $data]);

        $portal = PortalHelper::load($this->db, $instanceId);
        list($price, $currency) = PortalPricing::getPrice($portal);

        $this->assertEquals(840, $price);
        $this->assertEquals('EUR', $currency);
    }

    public function testPricePremium10LicenseUK()
    {
        $data = [
            'user_plan' => [
                'license'   => 10,
                'regional'  => 'UK',
                'product'   => 'premium'
            ]
        ];
        $instanceId = $this->createInstance($this->db, ['data' => $data]);

        $portal = PortalHelper::load($this->db, $instanceId);
        list($price, $currency) = PortalPricing::getPrice($portal);

        $this->assertEquals(720, $price);
        $this->assertEquals('GBP', $currency);
    }

    public function testPricePremium30LicenseAUUS()
    {
        $data = [
            'user_plan' => [
                'license'   => 30,
                'regional'  => 'AU',
                'product'   => 'premium'
            ]
        ];
        $instanceId = $this->createInstance($this->db, ['data' => $data]);

        $portal = PortalHelper::load($this->db, $instanceId);
        list($price, $currency) = PortalPricing::getPrice($portal);

        $this->assertEquals(2880, $price);
        $this->assertEquals('AUD', $currency);
    }

    public function testPricePremium30LicenseEU()
    {
        $data = [
            'user_plan' => [
                'license'   => 30,
                'regional'  => 'EU',
                'product'   => 'premium'
            ]
        ];
        $instanceId = $this->createInstance($this->db, ['data' => $data]);

        $portal = PortalHelper::load($this->db, $instanceId);
        list($price, $currency) = PortalPricing::getPrice($portal);

        $this->assertEquals(2160, $price);
        $this->assertEquals('EUR', $currency);
    }

    public function testPricePremium30LicenseUK()
    {
        $data = [
            'user_plan' => [
                'license'   => 30,
                'regional'  => 'UK',
                'product'   => 'premium'
            ]
        ];
        $instanceId = $this->createInstance($this->db, ['data' => $data]);

        $portal = PortalHelper::load($this->db, $instanceId);
        list($price, $currency) = PortalPricing::getPrice($portal);

        $this->assertEquals(1800, $price);
        $this->assertEquals('GBP', $currency);
    }

    public function testGetUserLimitationNumber()
    {
        $data = [
            'user_plan' => [
                'license'   => 30,
                'regional'  => 'UK',
                'product'   => 'premium'
            ]
        ];
        $instanceId = $this->createInstance($this->db, ['data' => $data]);

        $portal = PortalHelper::load($this->db, $instanceId);
        $userLimitationNumber = PortalPricing::getUserLimitationNumber($portal);

        $this->assertEquals(63, $userLimitationNumber);
    }

    public function testGetUserLimitationNumberLegacy()
    {
        $data = [
            'foo' => 'bar'
        ];
        $instanceId = $this->createInstance($this->db, ['data' => $data]);

        $portal = PortalHelper::load($this->db, $instanceId);
        $userLimitationNumber = PortalPricing::getUserLimitationNumber($portal);

        $this->assertEquals(-1, $userLimitationNumber);
    }

    public function testPortalHasPrice()
    {
        $data = [
            'user_plan' => [
                'license'   => 30,
                'regional'  => 'UK',
                'product'   => 'premium',
                'price'     => 10000,
                'currency'  => 'USD'
            ]
        ];
        $instanceId = $this->createInstance($this->db, ['data' => $data]);

        $portal = PortalHelper::load($this->db, $instanceId);
        list($price, $currency) = PortalPricing::getPrice($portal);

        $this->assertEquals(10000, $price);
        $this->assertEquals('USD', $currency);
        $portalPrice = PortalPricing::getPortalPrice($portal);
        $this->assertEquals(10000, $portalPrice);
    }

    public function testCountPortalUsers()
    {
        $instance = 'portal.mygo1.com';
        $this->createInstance($this->db, ['title' => $instance]);

        $this->createUser($this->db, ['mail' => 'user.0@instance.com', 'instance' => $instance]);
        $this->createUser($this->db, ['mail' => 'user.1@instance.com', 'instance' => $instance]);

        $i = 0;
        while ($i < 10) {
            $this->createUser($this->db, ['mail' => "user{$i}@instance.com", 'instance' => $instance]);
            $i++;
        }

        $count = PortalPricing::countPortalUsers($this->db, $instance);
        $this->assertEquals(10, $count);
    }

    public function testCountPortalActiveUsers()
    {
        $instance = 'portal.mygo1.com';
        $this->createInstance($this->db, ['title' => $instance]);

        $this->createUser($this->db, ['mail' => 'user.0@instance.com', 'instance' => $instance]);
        $this->createUser($this->db, ['mail' => 'user.1@instance.com', 'instance' => $instance]);

        $i = 0;
        while ($i < 10) {
            $this->createUser($this->db, ['mail' => "user{$i}@instance.com", 'instance' => $instance]);
            $i++;
        }

        $count = PortalPricing::countCurrentActiveUser($this->db, $instance, '-3 month');
        $this->assertEquals(10, $count);

        $count = PortalPricing::countCurrentActiveUser($this->db, $instance, 'now');
        $this->assertEquals(0, $count);
    }
}
