<?php

namespace go1\util\tests\lo;

use go1\util\lo\LoHelper;
use go1\util\tests\UtilTestCase;

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
