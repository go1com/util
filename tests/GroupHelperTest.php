<?php

namespace go1\util\tests;

use go1\util\GroupHelper;
use go1\util\schema\mock\GroupMockTrait;

class GroupHelperTest extends UtilTestCase
{
    use GroupMockTrait;

    public function setUp()
    {
        parent::setUp();
        $this->installGo1Schema($this->db, $coreOnly = false);
    }

    public function test()
    {
        $groupId = $this->createGroup($this->db);
        $this->createGroupItem($this->db, ['group_id' => $groupId, 'entity_type' => 'lo', 'entity_id' => 100]);
        $this->assertTrue(GroupHelper::isItemOf($this->db, 'lo', 100, $groupId));
        $this->assertFalse(GroupHelper::isItemOf($this->db, 'lo', 1001, $groupId));
    }
}
