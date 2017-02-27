<?php

namespace go1\util\tests;

use go1\util\GroupHelper;
use go1\util\GroupItemStatus;
use go1\util\schema\mock\GroupMockTrait;

class GroupHelperTest extends UtilTestCase
{
    use GroupMockTrait;

    public function setUp()
    {
        parent::setUp();
        $this->installGo1Schema($this->db, $coreOnly = false);
    }

    public function testIsItemOf()
    {
        $groupId = $this->createGroup($this->db);
        $this->createGroupItem($this->db, ['group_id' => $groupId, 'entity_type' => 'lo', 'entity_id' => 100]);
        $this->assertTrue(GroupHelper::isItemOf($this->db, 'lo', 100, $groupId));
        $this->assertFalse(GroupHelper::isItemOf($this->db, 'lo', 1001, $groupId));
    }

    public function testCanAccess()
    {
        $groupId = $this->createGroup($this->db);
        $this->createGroupItem($this->db, ['group_id' => $groupId, 'entity_type' => 'user', 'entity_id' => $user1Id = 25]);
        $this->assertTrue(GroupHelper::canAccess($this->db, $user1Id, $groupId));

        $this->createGroupItem($this->db, ['group_id' => $groupId, 'entity_type' => 'user', 'entity_id' => $user2Id = 52, 'status' => GroupItemStatus::PENDING]);
        $this->assertFalse(GroupHelper::canAccess($this->db, $user2Id, $groupId));
        $this->assertFalse(GroupHelper::canAccess($this->db, $userId = 404, $groupId));
    }
}
