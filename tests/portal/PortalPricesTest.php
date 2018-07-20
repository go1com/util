<?php

namespace go1\util\tests\portal;

use go1\util\portal\PortalHelper;
use go1\util\portal\PortalPricing;
use go1\util\schema\mock\PortalMockTrait;
use go1\util\tests\UtilTestCase;

class PortalPricingTest extends UtilTestCase
{
    use PortalMockTrait;

    public function testGetUserLimitationNumber()
    {
        $data = [
            'user_plan' => [
                'license'   => 30,
                'regional'  => 'UK',
                'product'   => 'premium'
            ]
        ];
        $instanceId = $this->createPortal($this->db, ['data' => $data]);

        $portal = PortalHelper::load($this->db, $instanceId);
        $userLimitationNumber = PortalPricing::getUserLimitationNumber($portal);

        $this->assertEquals(63, $userLimitationNumber);
    }

    public function testGetUserLimitationNumberLegacy()
    {
        $data = [
            'foo' => 'bar'
        ];
        $instanceId = $this->createPortal($this->db, ['data' => $data]);

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
        $instanceId = $this->createPortal($this->db, ['data' => $data]);

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
        $this->createPortal($this->db, ['title' => $instance]);

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
        $this->createPortal($this->db, ['title' => $instance]);

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
