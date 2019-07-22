<?php

namespace go1\util\tests\model;

use go1\util\edge\EdgeTypes;
use go1\util\model\User;
use go1\util\schema\mock\UserMockTrait;
use go1\util\tests\UtilCoreTestCase;
use go1\util\user\UserHelper;

class UserModelTest extends UtilCoreTestCase
{
    use UserMockTrait;

    private $mail      = 'abc@mail.com';
    private $profileId = 123;

    public function test()
    {
        $data = [
            'mail'       => $this->mail,
            'profile_id' => $this->profileId,
            'name'       => 'Bob Bay',
            'login'      => time(),
            'access'     => time(),
            'first_name' => 'Bob',
            'last_name'  => 'Bay',
            'status'     => 1,
        ];

        $userId = $this->createUser($this->go1, $data + ['instance' => 'accounts.com']);
        $account1Id = $this->createUser($this->go1, $data + ['instance' => $instance1 = '1.mygo1.com']);
        $account2Id = $this->createUser($this->go1, $data + ['instance' => $instance2 = '2.mygo1.com']);
        $this->link($this->go1, EdgeTypes::HAS_ACCOUNT, $userId, $account1Id);
        $this->link($this->go1, EdgeTypes::HAS_ACCOUNT, $userId, $account2Id);

        // Don't load accounts
        $root = UserHelper::load($this->go1, $userId);
        $user = User::create($root, $this->go1, $isRoot = false);

        $this->assertEquals($userId, $user->id);
        $this->assertEquals($data['mail'], $user->mail);
        # $this->assertEquals($data['name'], $rootModel->name);
        $this->assertEquals($data['login'], $user->login);
        $this->assertEquals($data['status'], $user->status);
        $this->assertEquals($data['access'], $user->access);
        $this->assertEquals($data['first_name'], $user->firstName);
        $this->assertEquals($data['last_name'], $user->lastName);
        $this->assertEquals($data['profile_id'], $user->profileId);
        $this->assertEquals(0, count($user->accounts));

        // Load sub accounts.
        $user = User::create($root, $this->go1, $isRoot = true, $instance1);
        $this->assertEquals(1, count($user->accounts));
        $this->assertEquals($account1Id, $user->accounts[0]->id);
    }

    public function testDiff()
    {
        $id = $this->createUser($this->go1, ['mail' => 'foo@bar.baz', 'instance' => 'qa.mygo1.com', 'first_name' => 'Foo']);
        $original = UserHelper::load($this->go1, $id);
        $originalModel = User::create($original);

        $user = clone $original;
        $this->assertCount(0, $originalModel->diff($user));

        $user->first_name = 'Bar';
        $diff = $originalModel->diff($user);
        $this->assertCount(1, $diff);
        $this->assertEquals('Foo', $diff['first_name']['source']);
        $this->assertEquals('Bar', $diff['first_name']['target']);
    }
}
