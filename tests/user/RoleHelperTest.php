<?php

namespace go1\util\tests\user;

use go1\util\edge\EdgeTypes;
use go1\util\Queue;
use go1\util\tests\UtilTestCase;
use go1\util\user\RoleHelper;

class RoleHelperTest extends UtilTestCase
{
    public function testAdd()
    {
        RoleHelper::add($this->db, $this->queue, 'instance.mygo1.com', 'admin');

        $message = $this->queueMessages[Queue::ROLE_CREATE][0];
        $this->assertEquals('instance.mygo1.com', $message['instance']);
        $this->assertEquals('admin', $message['name']);
    }

    public function TestRoleId()
    {
        $expectedRoleId = RoleHelper::add($this->db, $this->queue, 'instance.mygo1.com', 'admin');
        $roleId = RoleHelper::roleId($this->db, $this->queue, 'instance.mygo1.com', 'admin');

        $this->assertEquals($expectedRoleId, $roleId);
    }

    public function testGrant()
    {
        $roleId = RoleHelper::add($this->db, $this->queue, 'instance.mygo1.com', 'admin');
        RoleHelper::grant($this->db, $this->queue, 'instance.mygo1.com', 1000, 'admin');

        $message = $this->queueMessages[Queue::RO_CREATE][0];
        $this->assertEquals(EdgeTypes::HAS_ROLE, $message['type']);
        $this->assertEquals(1000, $message['source_id']);
        $this->assertEquals($roleId, $message['target_id']);
    }
}
