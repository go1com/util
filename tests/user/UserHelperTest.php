<?php

namespace go1\util\tests;

use go1\util\edge\EdgeTypes;
use go1\util\schema\mock\PortalMockTrait;
use go1\util\schema\mock\UserMockTrait;
use go1\util\Text;
use go1\util\user\Roles;
use go1\util\user\UserHelper;

class UserHelperTest extends UtilCoreTestCase
{
    use UserMockTrait;
    use PortalMockTrait;

    public function testLoad()
    {
        $id = $this->createUser($this->go1, ['mail' => 'foo@bar.baz', 'instance' => 'qa.mygo1.com']);

        $user = UserHelper::load($this->go1, $id);
        $this->assertEquals($id, $user->id);
        $this->assertEquals('foo@bar.baz', $user->mail);
        $this->assertEquals('qa.mygo1.com', $user->instance);
        $this->assertEquals(false, UserHelper::load($this->go1, 0));
        $this->assertEquals(false, UserHelper::load($this->go1, 999));
    }

    public function testLoadByInstance()
    {
        $id = $this->createUser($this->go1, ['mail' => 'foo@bar.baz', 'instance' => 'qa.mygo1.com']);

        $this->assertEquals(false, UserHelper::load($this->go1, 0, 'qa.mygo1.com'));
        $this->assertEquals(false, UserHelper::load($this->go1, 999, 'qa.mygo1.com'));
        $this->assertEquals(false, UserHelper::load($this->go1, 999, 'invalid.mygo1.com'));
        $this->assertEquals(false, UserHelper::load($this->go1, $id, 'invalid.mygo1.com'));

        $user = UserHelper::load($this->go1, $id, 'qa.mygo1.com');
        $this->assertEquals($id, $user->id);
        $this->assertEquals('foo@bar.baz', $user->mail);
        $this->assertEquals('qa.mygo1.com', $user->instance);

        $user = (array) UserHelper::load($this->go1, $id, 'qa.mygo1.com', 'mail');
        $this->assertCount(1, $user);
        $this->assertEquals('foo@bar.baz', $user['mail']);
    }

    public function testLoadByMail()
    {
        $id = $this->createUser($this->go1, ['mail' => 'foo@bar.baz', 'instance' => 'qa.mygo1.com', 'profile_id' => 10]);

        $this->assertEquals(false, UserHelper::loadByEmail($this->go1, 'qa.mygo1.com', 'invalid@email.com'));
        $this->assertEquals(false, UserHelper::loadByEmail($this->go1, 'invalid.mygo1.com', 'foo@bar.baz'));
        $this->assertEquals($id, UserHelper::loadByEmail($this->go1, 'qa.mygo1.com', 'foo@bar.baz')->id);

        $user = (array) UserHelper::loadByEmail($this->go1, 'qa.mygo1.com', 'foo@bar.baz', 'profile_id');
        $this->assertCount(1, $user);
        $this->assertEquals(10, $user['profile_id']);
    }

    public function testLoadByProfileId()
    {
        $id = $this->createUser($this->go1, ['mail' => 'foo@bar.baz', 'instance' => 'qa.mygo1.com', 'profile_id' => 10]);

        $this->assertEquals(false, UserHelper::loadByEmail($this->go1, 'qa.mygo1.com', 'invalid@email.com'));
        $this->assertEquals(false, UserHelper::loadByEmail($this->go1, 'invalid.mygo1.com', 'foo@bar.baz'));
        $this->assertEquals($id, UserHelper::loadByEmail($this->go1, 'qa.mygo1.com', 'foo@bar.baz')->id);

        $user = (array) UserHelper::loadByProfileId($this->go1, 10, 'qa.mygo1.com', 'mail');
        $this->assertCount(1, $user);
        $this->assertEquals('foo@bar.baz', $user['mail']);
    }

    public function testLoadByProfileIdFromDBView()
    {
        $this->createUser($this->go1, ['mail' => 'foo@bar.baz', 'instance' => 'accounts.test', 'profile_id' => 101]);
        $user = UserHelper::loadUserByProfileId($this->go1, 101);
        $this->assertEquals('foo@bar.baz', $user->mail);
    }

    public function testInstanceIds()
    {
        $instance1Id = $this->createPortal($this->go1, ['title' => $instance1Name = 'a1@mygo1.com']);
        $instance2Id = $this->createPortal($this->go1, ['title' => $instance2Name = 'a2@mygo1.com']);
        $this->createPortal($this->go1, ['title' => 'a3@mygo1.com']);
        $this->createUser($this->go1, ['mail' => $email = 'user@mail.com', 'instance' => $instance1Name]);
        $this->createUser($this->go1, ['mail' => $email, 'instance' => $instance2Name]);

        $instanceIds = UserHelper::userInstanceIds($this->go1, $email);
        $this->assertEquals(2, count($instanceIds));
        $this->assertEquals($instance1Id, $instanceIds[0]);
        $this->assertEquals($instance2Id, $instanceIds[1]);

        $instanceIds = UserHelper::userInstanceIds($this->go1, 'none@mail.com');
        $this->assertEquals(0, count($instanceIds));
    }

    public function testJwt()
    {
        $userId = $this->createUser($this->go1, ['mail' => 'user@some.where', 'instance' => 'accounts.local']);
        $accountId = $this->createUser($this->go1, ['mail' => 'user@some.where', 'instance' => 'qa.mygo1.com']);
        $this->link($this->go1, EdgeTypes::HAS_ACCOUNT, $userId, $accountId);
        $jwt = $this->jwtForUser($this->go1, $userId, 'qa.mygo1.com');
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

    public function dataUserEmbedded()
    {
        return [
            [['embedded' => ['portal' => ['status' => 0]]], false],
            [['embedded' => ['portal' => ['status' => 1]]], true],
            [['embedded' => ['portal' => [
                0 => ['status' => 1]
            ]]], true],
            [['embedded' => ['portal' => [
                ['status' => 0],
                ['status' => 1],
            ]]], false],
        ];
    }

    /** @dataProvider dataUserEmbedded */
    public function testIsEmbeddedPortalActive(array $user, bool $valid = true)
    {
        $user = json_decode(json_encode($user));
        $this->assertEquals($valid, UserHelper::isEmbeddedPortalActive($user));
    }
}
