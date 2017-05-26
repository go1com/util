<?php

namespace go1\util\tests\lo;

use DateTime;
use go1\util\edge\EdgeTypes;
use go1\util\lo\LiTypes;
use go1\util\lo\LoHelper;
use go1\util\schema\mock\InstanceMockTrait;
use go1\util\schema\mock\LoMockTrait;
use go1\util\schema\mock\UserMockTrait;
use go1\util\tests\UtilTestCase;
use HTMLPurifier;

class LoHelperPremiumTest extends UtilTestCase
{

    public function setUp()
    {
        parent::setUp();

        $this->db->insert('gc_lo_group', ['lo_id' => 1000, 'instance_id' => 10000]);
        $this->db->insert('gc_lo_group', ['lo_id' => 2000, 'instance_id' => 10000]);
    }

    public function testIsPremium()
    {
        $this->assertTrue(LoHelper::isBelongToGroup($this->db, 1000, 10000));
        $this->assertTrue(LoHelper::isBelongToGroup($this->db, 2000, 10000));
        $this->assertFalse(LoHelper::isBelongToGroup($this->db, 3000, 10000));
        $this->assertFalse(LoHelper::isBelongToGroup($this->db, 1000, 10001));
    }
}
