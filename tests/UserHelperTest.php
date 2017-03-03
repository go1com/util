<?php

namespace go1\util\tests;

use go1\util\schema\mock\InstanceMockTrait;
use go1\util\schema\mock\UserMockTrait;
use go1\util\UserHelper;

class UserHelperTest extends UtilTestCase
{
    use UserMockTrait;
    use InstanceMockTrait;

    public function testInstanceIds()
    {
        $instance1Id = $this->createInstance($this->db, ['title' => $instance1Name = 'a1@mygo1.com']);
        $instance2Id = $this->createInstance($this->db, ['title' => $instance2Name = 'a2@mygo1.com']);
        $this->createInstance($this->db, ['title' => 'a3@mygo1.com']);

        $this->createUser($this->db, ['mail' => $email = 'user@mail.com', 'instance' => $instance1Name]);
        $this->createUser($this->db, ['mail' => $email, 'instance' => $instance2Name]);

        $instanceIds = UserHelper::userInstanceIds($this->db, $email);
        $this->assertEquals(2, count($instanceIds));
        $this->assertEquals($instance1Id, $instanceIds[0]);
        $this->assertEquals($instance2Id, $instanceIds[1]);

        $instanceIds = UserHelper::userInstanceIds($this->db, 'none@mail.com');
        $this->assertEquals(0, count($instanceIds));
    }
}
