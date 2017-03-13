<?php

namespace go1\util\tests\model;

use go1\util\edge\EdgeTypes;
use go1\util\model\User;
use go1\util\schema\mock\UserMockTrait;
use go1\util\tests\UtilTestCase;
use go1\util\user\UserHelper;

class UserModelTest extends UtilTestCase
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

        $rootId = $this->createUser($this->db, $data + ['instance' => 'accounts.com']);
        $sub1Id = $this->createUser($this->db, $data + ['instance' => $instance1 = '1.mygo1.com']);
        $sub2Id = $this->createUser($this->db, $data + ['instance' => $instance2 = '2.mygo1.com']);
        $this->link($this->db, EdgeTypes::HAS_ACCOUNT, $rootId, $sub1Id);
        $this->link($this->db, EdgeTypes::HAS_ACCOUNT, $rootId, $sub2Id);

        // Dont load sub accounts
        $root = UserHelper::load($this->db, $rootId);
        $rootModel = User::create($root, $this->db, $isRoot = false);

        $this->assertEquals($rootId, $rootModel->id);
        $this->assertEquals($data['mail'], $rootModel->mail);
        $this->assertEquals($data['name'], $rootModel->name);
        $this->assertEquals($data['login'], $rootModel->login);
        $this->assertEquals($data['status'], $rootModel->status);
        $this->assertEquals($data['access'], $rootModel->access);
        $this->assertEquals($data['first_name'], $rootModel->firstName);
        $this->assertEquals($data['last_name'], $rootModel->lastName);
        $this->assertEquals($data['profile_id'], $rootModel->profileId);
        $this->assertEquals(0, count($rootModel->accounts));

        // Load sub accounts.
        $rootModel = User::create($root, $this->db, $isRoot = true, $instance1);
        $this->assertEquals(1, count($rootModel->accounts));
        $this->assertEquals($sub1Id, $rootModel->accounts[0]->id);
    }
}
