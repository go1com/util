<?php

namespace go1\util\tests;

use go1\util\edge\EdgeTypes;
use go1\util\schema\mock\PortalMockTrait;
use go1\util\schema\mock\UserMockTrait;
use go1\util\Text;
use go1\util\user\Roles;
use go1\util\user\UserHelper;

class UserHelperTest extends UtilTestCase
{
    use UserMockTrait;
    use PortalMockTrait;

    public function testLoad()
    {
        $id = $this->createUser($this->db, ['mail' => 'foo@bar.baz', 'instance' => 'qa.mygo1.com']);

        $user = UserHelper::load($this->db, $id);
        $this->assertEquals($id, $user->id);
        $this->assertEquals('foo@bar.baz', $user->mail);
        $this->assertEquals('qa.mygo1.com', $user->instance);
        $this->assertEquals(false, UserHelper::load($this->db, 0));
        $this->assertEquals(false, UserHelper::load($this->db, 999));
    }

    public function testLoadByInstance()
    {
        $id = $this->createUser($this->db, ['mail' => 'foo@bar.baz', 'instance' => 'qa.mygo1.com']);

        $this->assertEquals(false, UserHelper::load($this->db, 0, 'qa.mygo1.com'));
        $this->assertEquals(false, UserHelper::load($this->db, 999, 'qa.mygo1.com'));
        $this->assertEquals(false, UserHelper::load($this->db, 999, 'invalid.mygo1.com'));
        $this->assertEquals(false, UserHelper::load($this->db, $id, 'invalid.mygo1.com'));

        $user = UserHelper::load($this->db, $id, 'qa.mygo1.com');
        $this->assertEquals($id, $user->id);
        $this->assertEquals('foo@bar.baz', $user->mail);
        $this->assertEquals('qa.mygo1.com', $user->instance);

        $user = (array) UserHelper::load($this->db, $id, 'qa.mygo1.com', 'mail');
        $this->assertCount(1, $user);
        $this->assertEquals('foo@bar.baz', $user['mail']);
    }

    public function testLoadByMail()
    {
        $id = $this->createUser($this->db, ['mail' => 'foo@bar.baz', 'instance' => 'qa.mygo1.com', 'profile_id' => 10]);

        $this->assertEquals(false, UserHelper::loadByEmail($this->db, 'qa.mygo1.com', 'invalid@email.com'));
        $this->assertEquals(false, UserHelper::loadByEmail($this->db, 'invalid.mygo1.com', 'foo@bar.baz'));
        $this->assertEquals($id, UserHelper::loadByEmail($this->db, 'qa.mygo1.com', 'foo@bar.baz')->id);

        $user = (array) UserHelper::loadByEmail($this->db, 'qa.mygo1.com', 'foo@bar.baz', 'profile_id');
        $this->assertCount(1, $user);
        $this->assertEquals(10, $user['profile_id']);
    }

    public function testLoadByProfileId()
    {
        $id = $this->createUser($this->db, ['mail' => 'foo@bar.baz', 'instance' => 'qa.mygo1.com', 'profile_id' => 10]);

        $this->assertEquals(false, UserHelper::loadByEmail($this->db, 'qa.mygo1.com', 'invalid@email.com'));
        $this->assertEquals(false, UserHelper::loadByEmail($this->db, 'invalid.mygo1.com', 'foo@bar.baz'));
        $this->assertEquals($id, UserHelper::loadByEmail($this->db, 'qa.mygo1.com', 'foo@bar.baz')->id);

        $user = (array) UserHelper::loadByProfileId($this->db, 10, 'qa.mygo1.com', 'mail');
        $this->assertCount(1, $user);
        $this->assertEquals('foo@bar.baz', $user['mail']);
    }

    public function testInstanceIds()
    {
        $instance1Id = $this->createPortal($this->db, ['title' => $instance1Name = 'a1@mygo1.com']);
        $instance2Id = $this->createPortal($this->db, ['title' => $instance2Name = 'a2@mygo1.com']);
        $this->createPortal($this->db, ['title' => 'a3@mygo1.com']);
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


    public function dataIsStaff()
    {
        return [
            [],
            [[Roles::AUTHENTICATED]],
            [[Roles::AUTHENTICATED, Roles::STUDENT]],
            [[Roles::AUTHENTICATED, Roles::MANAGER]],
            [[Roles::AUTHENTICATED, Roles::TUTOR]],
            [[Roles::AUTHENTICATED, Roles::ADMIN]],
            [[Roles::AUTHENTICATED, Roles::TAM], true],
            [[Roles::AUTHENTICATED, Roles::DEVELOPER], true],
            [[Roles::AUTHENTICATED, Roles::ROOT], true],
        ];
    }

    /** @dataProvider dataIsStaff */
    public function testIsStaff(array $roles = null, $valid = false)
    {
        $this->assertEquals($valid, UserHelper::isStaff($roles));
    }
}
