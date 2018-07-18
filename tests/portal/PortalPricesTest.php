<?php

namespace go1\util\tests\portal;

use go1\util\portal\PortalHelper;
use go1\util\portal\PortalPricing;
use go1\util\schema\mock\PortalMockTrait;
use go1\util\tests\UtilCoreTestCase;

class PortalPricingTest extends UtilCoreTestCase
{
    use PortalMockTrait;

    private $formula = [
        [
            'op'               => '<=',
            'license_limit'    => 10,
            'license_multiple' => false,
            'price'            => [
                'AU' => ['currency' => 'AUD', 'price' => 20],
                'EU' => ['currency' => 'EUR', 'price' => 18],
            ],
        ],
        [
            'op'               => '>',
            'license_limit'    => 10,
            'license_multiple' => true,
            'price'            => [
                'AU' => ['currency' => 'AUD', 'price' => 15],
                'EU' => ['currency' => 'EUR', 'price' => 13],
            ],
        ],
    ];

    public function testPricePlatform()
    {
        $data = [
            'user_plan' => [
                'license' => 5,
            ],
        ];
        $instanceId = $this->createPortal($this->db, ['data' => $data]);

        $portal = PortalHelper::load($this->db, $instanceId);
        list($price, $currency) = PortalPricing::getPrice($portal);

        $this->assertEquals(0, $price);
        $this->assertEquals('AUD', $currency);
    }

    public function testPricePremium10LicenseAU()
    {
        $data = [
            'user_plan' => [
                'license'  => 10,
                'regional' => 'AU',
                'product'  => 'premium',
            ],
        ];
        $instanceId = $this->createPortal($this->db, ['data' => $data]);

        $portal = PortalHelper::load($this->db, $instanceId);
        list($price, $currency) = PortalPricing::getPrice($portal, true, $this->formula);

        $this->assertEquals(20, $price);
        $this->assertEquals('AUD', $currency);
    }

    public function testPricePremium10LicenseEU()
    {
        $data = [
            'user_plan' => [
                'license'  => 10,
                'regional' => 'EU',
                'product'  => 'premium',
            ],
        ];
        $instanceId = $this->createPortal($this->db, ['data' => $data]);

        $portal = PortalHelper::load($this->db, $instanceId);
        list($price, $currency) = PortalPricing::getPrice($portal, true, $this->formula);

        $this->assertEquals(18, $price);
        $this->assertEquals('EUR', $currency);
    }

    public function testPricePremium30LicenseAU()
    {
        $data = [
            'user_plan' => [
                'license'  => 30,
                'regional' => 'AU',
                'product'  => 'premium',
            ],
        ];
        $instanceId = $this->createPortal($this->db, ['data' => $data]);

        $portal = PortalHelper::load($this->db, $instanceId);
        list($price, $currency) = PortalPricing::getPrice($portal, true, $this->formula);

        $this->assertEquals(450, $price);
        $this->assertEquals('AUD', $currency);
    }

    public function testPricePremium30LicenseEU()
    {
        $data = [
            'user_plan' => [
                'license'  => 30,
                'regional' => 'EU',
                'product'  => 'premium',
            ],
        ];
        $instanceId = $this->createPortal($this->db, ['data' => $data]);

        $portal = PortalHelper::load($this->db, $instanceId);
        list($price, $currency) = PortalPricing::getPrice($portal, true, $this->formula);

        $this->assertEquals(390, $price);
        $this->assertEquals('EUR', $currency);
    }

    public function testGetUserLimitationNumber()
    {
        $data = [
            'user_plan' => [
                'license'  => 30,
                'regional' => 'UK',
                'product'  => 'premium',
            ],
        ];
        $instanceId = $this->createPortal($this->db, ['data' => $data]);

        $portal = PortalHelper::load($this->db, $instanceId);
        $userLimitationNumber = PortalPricing::getUserLimitationNumber($portal);

        $this->assertEquals(63, $userLimitationNumber);
    }

    public function testGetUserLimitationNumberLegacy()
    {
        $data = [
            'foo' => 'bar',
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
                'license'  => 30,
                'regional' => 'UK',
                'product'  => 'premium',
                'price'    => 10000,
                'currency' => 'USD',
            ],
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
