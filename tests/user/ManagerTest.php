<?php

namespace go1\util\tests;

use go1\util\edge\EdgeHelper;
use go1\util\edge\EdgeTypes;
use go1\util\schema\mock\UserMockTrait;
use go1\util\user\ManagerHelper;

class ManagerTest extends UtilTestCase
{
    use UserMockTrait;

    public function test()
    {
        // Setup data
        $userId = $this->createUser($this->db, ['instance' => 'accounts.gocatalyze.com', 'mail' => 'student@qa.mygo1.com']);
        $managerUserId = $this->createUser($this->db, ['instance' => 'accounts.gocatalyze.com', 'mail' => 'manager@qa.mygo1.com']);
        $managerAccountId = $this->createUser($this->db, ['instance' => 'qa.mygo1.com', 'mail' => 'manager@qa.mygo1.com']);
        EdgeHelper::link($this->db, $this->queue, EdgeTypes::HAS_ACCOUNT, $managerUserId, $managerAccountId);
        EdgeHelper::link($this->db, $this->queue, EdgeTypes::HAS_MANAGER, $userId, $managerAccountId);

        // Check
        $this->assertTrue(ManagerHelper::isManager($this->db, 'qa.mygo1.com', $managerAccountId, $userId));
        $this->assertFalse(ManagerHelper::isManager($this->db, 'qa.mygo1.com', $managerAccountId + 9, $userId));
        $this->assertFalse(ManagerHelper::isManager($this->db, 'qa.mygo1.com', $managerAccountId, $userId + 9));
    }
}
