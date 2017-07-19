<?php

namespace go1\util\tests\lo;

use go1\util\group\GroupItemStatus;
use go1\util\lo\LoHelper;
use go1\util\tests\UtilTestCase;

class LoHelperPremiumTest extends UtilTestCase
{
    public function testIsPremium()
    {
        $this->db->insert('gc_lo_group', ['lo_id' => 1000, 'instance_id' => 10000]);
        $this->db->insert('gc_lo_group', ['lo_id' => 2000, 'instance_id' => 10000]);

        $this->assertTrue(LoHelper::isBelongToGroup($this->db, 1000, 10000));
        $this->assertTrue(LoHelper::isBelongToGroup($this->db, 2000, 10000));
        $this->assertFalse(LoHelper::isBelongToGroup($this->db, 3000, 10000));
        $this->assertFalse(LoHelper::isBelongToGroup($this->db, 1000, 10001));
    }

    public function testActiveMembershipIds()
    {
        $portal1 = 333;
        $portal2 = $portal1 + 1;
        $group1 = 123;
        $group2 = $group1 + 1;
        $group3 = $group1 + 2;
        $course1 = 234;
        $course2 = $course1 + 1;
        $course3 = $course1 + 2;
        $course4 = $course1 + 3;

        # Group 1: portal 1 & course 1 & course 3
        $this->db->insert('social_group_item', ['group_id' => $group1, 'created' => time(), 'updated' => time(), 'entity_type' => 'portal', 'entity_id' => $portal1, 'status' => GroupItemStatus::ACTIVE]);
        $this->db->insert('social_group_item', ['group_id' => $group1, 'created' => time(), 'updated' => time(), 'entity_type' => 'lo', 'entity_id' => $course1, 'status' => GroupItemStatus::ACTIVE]);
        $this->db->insert('social_group_item', ['group_id' => $group1, 'created' => time(), 'updated' => time(), 'entity_type' => 'lo', 'entity_id' => $course3, 'status' => GroupItemStatus::ACTIVE]);
        $this->assertEquals([$portal1], LoHelper::activeMembershipIds($this->db, $course1));
        $this->assertEquals([$portal1], LoHelper::activeMembershipIds($this->db, $course3));

        # Group 2: Portal 2 & course 2
        $this->db->insert('social_group_item', ['group_id' => $group2, 'created' => time(), 'updated' => time(), 'entity_type' => 'portal', 'entity_id' => $portal2, 'status' => GroupItemStatus::ACTIVE]);
        $this->db->insert('social_group_item', ['group_id' => $group2, 'created' => time(), 'updated' => time(), 'entity_type' => 'lo', 'entity_id' => $course2, 'status' => GroupItemStatus::ACTIVE]);
        $this->assertEquals([$portal2], LoHelper::activeMembershipIds($this->db, $course2));

        # Group 3: contains everything.
        $this->db->insert('social_group_item', ['group_id' => $group3, 'created' => time(), 'updated' => time(), 'entity_type' => 'portal', 'entity_id' => $portal1, 'status' => GroupItemStatus::ACTIVE]);
        $this->db->insert('social_group_item', ['group_id' => $group3, 'created' => time(), 'updated' => time(), 'entity_type' => 'portal', 'entity_id' => $portal2, 'status' => GroupItemStatus::ACTIVE]);
        $this->db->insert('social_group_item', ['group_id' => $group3, 'created' => time(), 'updated' => time(), 'entity_type' => 'lo', 'entity_id' => $course1, 'status' => GroupItemStatus::ACTIVE]);
        $this->db->insert('social_group_item', ['group_id' => $group3, 'created' => time(), 'updated' => time(), 'entity_type' => 'lo', 'entity_id' => $course2, 'status' => GroupItemStatus::ACTIVE]);
        $this->db->insert('social_group_item', ['group_id' => $group3, 'created' => time(), 'updated' => time(), 'entity_type' => 'lo', 'entity_id' => $course3, 'status' => GroupItemStatus::ACTIVE]);
        $this->db->insert('social_group_item', ['group_id' => $group3, 'created' => time(), 'updated' => time(), 'entity_type' => 'lo', 'entity_id' => $course4, 'status' => GroupItemStatus::ACTIVE]);

        $this->assertEquals([$portal1, $portal2], LoHelper::activeMembershipIds($this->db, $course1));
        $this->assertEquals([$portal2, $portal1], LoHelper::activeMembershipIds($this->db, $course2));
        $this->assertEquals([$portal1, $portal2], LoHelper::activeMembershipIds($this->db, $course3));
        $this->assertEquals([$portal1, $portal2], LoHelper::activeMembershipIds($this->db, $course4));
    }
}
