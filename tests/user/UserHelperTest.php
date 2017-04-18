<?php

namespace go1\util\tests;

use go1\util\edge\EdgeTypes;
use go1\util\schema\mock\InstanceMockTrait;
use go1\util\schema\mock\UserMockTrait;
use go1\util\Text;
use go1\util\user\UserHelper;

class UserHelperTest extends UtilTestCase
{
    use UserMockTrait;
    use InstanceMockTrait;

    public function testLoadByMail()
    {
        $id = $this->createUser($this->db, ['mail' => 'foo@bar.baz', 'instance' => 'qa.mygo1.com']);

        $this->assertEquals(false, UserHelper::loadByEmail($this->db, 'qa.mygo1.com', 'invalid@email.com'));
        $this->assertEquals(false, UserHelper::loadByEmail($this->db, 'invalid.mygo1.com', 'foo@bar.baz'));
        $this->assertEquals($id, UserHelper::loadByEmail($this->db, 'qa.mygo1.com', 'foo@bar.baz')->id);
    }

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

    public function testJwt()
    {
        $userId = $this->createUser($this->db, ['mail' => 'user@some.where', 'instance' => 'accounts.local']);
        $accountId = $this->createUser($this->db, ['mail' => 'user@some.where', 'instance' => 'qa.mygo1.com']);
        $this->link($this->db, EdgeTypes::HAS_ACCOUNT, $userId, $accountId);
        $jwt = $this->jwtForUser($this->db, $userId, 'qa.mygo1.com');
        $user = Text::jwtContent($jwt)->object->content;

        $this->assertEquals($userId, $user->id);
        $this->assertEquals($accountId, $user->accounts[0]->id);
    }
}
