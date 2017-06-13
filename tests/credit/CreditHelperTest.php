<?php

namespace go1\util\tests;

use go1\credit\domain\model\Credit;
use go1\util\schema\mock\CreditMockTrait;
use go1\util\credit\CreditHelper;

class CreditHelperTest extends UtilTestCase
{
    use CreditMockTrait;

    public function testCount()
    {
        $ownerId = 123;
        $productType = 'lo';
        $productId = 234;

        $this->createCredit($this->db, ['owner_id' => $ownerId, 'product_type' => $productType, 'product_id' => $productId, 'status' => Credit::STATUS_AVAILABLE]);
        $this->createCredit($this->db, ['owner_id' => $ownerId, 'product_type' => $productType, 'product_id' => $productId, 'status' => Credit::STATUS_DISABLED]);
        $this->createCredit($this->db, ['owner_id' => $ownerId, 'product_type' => $productType, 'product_id' => $productId, 'status' => Credit::STATUS_AVAILABLE]);
        $this->createCredit($this->db, ['owner_id' => $ownerId, 'product_type' => $productType, 'product_id' => $productId, 'status' => Credit::STATUS_USED]);
        $this->createCredit($this->db, ['owner_id' => $ownerId, 'product_type' => $productType, 'product_id' => $productId, 'status' => Credit::STATUS_DISABLED]);
        $this->createCredit($this->db, ['owner_id' => $ownerId, 'product_type' => $productType, 'product_id' => $productId, 'status' => Credit::STATUS_USED]);
        $this->createCredit($this->db, ['owner_id' => $ownerId, 'product_type' => $productType, 'product_id' => $productId, 'status' => Credit::STATUS_AVAILABLE]);

        $this->assertEquals(5, CreditHelper::total($this->db, $ownerId, $productId, $productType));
        $this->assertEquals(2, CreditHelper::used($this->db, $ownerId, $productId, $productType));
        $this->assertEquals(3, CreditHelper::remaining($this->db, $ownerId, $productId, $productType));
    }
}
