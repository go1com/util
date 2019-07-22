<?php

namespace go1\util\tests\user;

use go1\util\edge\EdgeTypes;
use go1\util\queue\Queue;
use go1\util\tests\UtilCoreTestCase;
use go1\util\user\RoleHelper;

class RoleHelperTest extends UtilCoreTestCase
{
    private $portalName = 'qa.mygo1.com';

    public function testAdd()
    {
        RoleHelper::add($this->go1, $this->queue, $this->portalName, 'admin');

        $message = $this->queueMessages[Queue::ROLE_CREATE][0];
        $this->assertEquals($this->portalName, $message['instance']);
        $this->assertEquals('admin', $message['name']);
    }

    public function TestRoleId()
    {
        $expectedRoleId = RoleHelper::add($this->go1, $this->queue, $this->portalName, 'admin');
        $roleId = RoleHelper::roleId($this->go1, $this->queue, $this->portalName, 'admin');

        $this->assertEquals($expectedRoleId, $roleId);
    }

    public function testGrant()
    {
        $roleId = RoleHelper::add($this->go1, $this->queue, $this->portalName, 'admin');
        RoleHelper::grant($this->go1, $this->queue, $this->portalName, 1000, 'admin');

        $message = $this->queueMessages[Queue::RO_CREATE][0];
        $this->assertEquals(EdgeTypes::HAS_ROLE, $message['type']);
        $this->assertEquals(1000, $message['source_id']);
        $this->assertEquals($roleId, $message['target_id']);
    }

    public function testRoleIds()
    {
        $adminRoleId = RoleHelper::add($this->go1, $this->queue, $this->portalName, 'admin');
        $studentRoleId = RoleHelper::add($this->go1, $this->queue, $this->portalName, 'student');

        $ids = RoleHelper::roleIds($this->go1, $this->portalName, ['admin', 'student']);
        $this->assertTrue(in_array($adminRoleId, $ids));
        $this->assertTrue(in_array($studentRoleId, $ids));

        $emptyIds = RoleHelper::roleIds($this->go1, $this->portalName, ['wrong role']);
        $this->assertEmpty($emptyIds);
    }
}
