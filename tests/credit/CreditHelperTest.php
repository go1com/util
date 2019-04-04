<?php

namespace go1\util\tests;

use go1\util\credit\CreditHelper;
use go1\util\credit\CreditStatuses;
use go1\util\schema\mock\CreditMockTrait;

class CreditHelperTest extends UtilTestCase
{
    use CreditMockTrait;

    public function testCount()
    {
        $ownerId = 123;
        $productType = 'lo';
        $productId = 234;

        $this->createCredit($this->go1, ['owner_id' => $ownerId, 'product_type' => $productType, 'product_id' => $productId, 'status' => CreditStatuses::STATUS_AVAILABLE]);
        $this->createCredit($this->go1, ['owner_id' => $ownerId, 'product_type' => $productType, 'product_id' => $productId, 'status' => CreditStatuses::STATUS_DISABLED]);
        $this->createCredit($this->go1, ['owner_id' => $ownerId, 'product_type' => $productType, 'product_id' => $productId, 'status' => CreditStatuses::STATUS_AVAILABLE]);
        $this->createCredit($this->go1, ['owner_id' => $ownerId, 'product_type' => $productType, 'product_id' => $productId, 'status' => CreditStatuses::STATUS_USED]);
        $this->createCredit($this->go1, ['owner_id' => $ownerId, 'product_type' => $productType, 'product_id' => $productId, 'status' => CreditStatuses::STATUS_DISABLED]);
        $this->createCredit($this->go1, ['owner_id' => $ownerId, 'product_type' => $productType, 'product_id' => $productId, 'status' => CreditStatuses::STATUS_USED]);
        $this->createCredit($this->go1, ['owner_id' => $ownerId, 'product_type' => $productType, 'product_id' => $productId, 'status' => CreditStatuses::STATUS_AVAILABLE]);

        $this->assertEquals(5, CreditHelper::total($this->go1, $ownerId, $productId, $productType));
        $this->assertEquals(2, CreditHelper::used($this->go1, $ownerId, $productId, $productType));
        $this->assertEquals(3, CreditHelper::remaining($this->go1, $ownerId, $productId, $productType));
    }
}
