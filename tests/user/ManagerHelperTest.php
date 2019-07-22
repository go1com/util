<?php

namespace go1\util\tests;

use go1\util\edge\EdgeHelper;
use go1\util\edge\EdgeTypes;
use go1\util\schema\mock\PortalMockTrait;
use go1\util\schema\mock\UserMockTrait;
use go1\util\user\ManagerHelper;
use go1\util\user\Roles;

class ManagerHelperTest extends UtilCoreTestCase
{
    use PortalMockTrait;
    use UserMockTrait;

    public function testIsManagerOfUser()
    {
        // Setup data
        $userId = $this->createUser($this->go1, ['instance' => 'accounts.gocatalyze.com', 'mail' => 'student@qa.mygo1.com']);
        $accountId = $this->createUser($this->go1, ['instance' => 'qa.mygo1.com', 'mail' => 'student@qa.mygo1.com']);
        $managerUserId = $this->createUser($this->go1, ['instance' => 'accounts.gocatalyze.com', 'mail' => 'manager@qa.mygo1.com']);

        EdgeHelper::link($this->go1, $this->queue, EdgeTypes::HAS_ACCOUNT, $userId, $accountId);
        EdgeHelper::link($this->go1, $this->queue, EdgeTypes::HAS_MANAGER, $accountId, $managerUserId);

        // Check
        $this->assertTrue(ManagerHelper::isManagerOfUser($this->go1, 'qa.mygo1.com', $managerUserId, $userId));
        $this->assertFalse(ManagerHelper::isManagerOfUser($this->go1, 'qa.mygo1.com', $managerUserId + 9, $userId));
        $this->assertFalse(ManagerHelper::isManagerOfUser($this->go1, 'qa.mygo1.com', $managerUserId, $userId + 9));
    }

    public function testIsManagerUser()
    {
        // Setup data
        $this->createPortal($this->go1, ['title' => 'az.mygo1.com']);
        $managerRoleId = $this->createRole($this->go1, ['instance' => 'az.mygo1.com', 'name' => Roles::MANAGER]);
        $managerAccountId = $this->createUser($this->go1, ['instance' => 'az.mygo1.com', 'mail' => 'manager@qa.mygo1.com']);
        EdgeHelper::link($this->go1, $this->queue, EdgeTypes::HAS_ROLE, $managerAccountId, $managerRoleId);

        // Check
        $this->assertTrue(ManagerHelper::isManagerUser($this->go1, $managerAccountId, 'az.mygo1.com'));
        $this->assertFalse(ManagerHelper::isManagerUser($this->go1, $managerAccountId, 'qa.mygo1.com'));
    }

    public function testUserManagerIds()
    {
        // Setup data
        $managerUserId = $this->createUser($this->go1, ['instance' => 'accounts.gocatalyze.com', 'mail' => 'manager@qa.mygo1.com']);
        $managerUserId2 = $this->createUser($this->go1, ['instance' => 'accounts.gocatalyze.com', 'mail' => 'manager2@qa.mygo1.com']);
        $accountId = $this->createUser($this->go1, ['instance' => 'qa.gocatalyze.com', 'mail' => 'student@qa.mygo1.com']);

        $this->link($this->go1, EdgeTypes::HAS_MANAGER, $accountId, $managerUserId);
        $this->link($this->go1, EdgeTypes::HAS_MANAGER, $accountId, $managerUserId2);

        // Check
        $this->assertEquals([$managerUserId, $managerUserId2], ManagerHelper::userManagerIds($this->go1, $accountId));
    }
}
