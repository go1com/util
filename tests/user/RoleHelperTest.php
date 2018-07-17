<?php

namespace go1\util\tests\user;

use go1\util\edge\EdgeTypes;
use go1\util\queue\Queue;
use go1\util\tests\UtilTestCase;
use go1\util\user\RoleHelper;

class RoleHelperTest extends UtilTestCase
{
    private $instance = 'qa.mygo1.com';

    public function testAdd()
    {
        RoleHelper::add($this->db, $this->queue, $this->instance, 'admin');

        $message = $this->queueMessages[Queue::ROLE_CREATE][0];
        $this->assertEquals($this->instance, $message['instance']);
        $this->assertEquals('admin', $message['name']);
    }

    public function TestRoleId()
    {
        $expectedRoleId = RoleHelper::add($this->db, $this->queue, $this->instance, 'admin');
        $roleId = RoleHelper::roleId($this->db, $this->queue, $this->instance, 'admin');

        $this->assertEquals($expectedRoleId, $roleId);
    }

    public function testGrant()
    {
        $roleId = RoleHelper::add($this->db, $this->queue, $this->instance, 'admin');
        RoleHelper::grant($this->db, $this->queue, $this->instance, 1000, 'admin');

        $message = $this->queueMessages[Queue::RO_CREATE][0];
        $this->assertEquals(EdgeTypes::HAS_ROLE, $message['type']);
        $this->assertEquals(1000, $message['source_id']);
        $this->assertEquals($roleId, $message['target_id']);
    }

    public function testRoleIds()
    {
        $adminRoleId = RoleHelper::add($this->db, $this->queue, $this->instance, 'admin');
        $studentRoleId = RoleHelper::add($this->db, $this->queue, $this->instance, 'student');

        $ids = RoleHelper::roleIds($this->db, $this->instance, ['admin', 'student']);
        $this->assertTrue(in_array($adminRoleId, $ids));
        $this->assertTrue(in_array($studentRoleId, $ids));

        $emptyIds = RoleHelper::roleIds($this->db, $this->instance, ['wrong role']);
        $this->assertEmpty($emptyIds);
    }
}
