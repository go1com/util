<?php

namespace go1\util\tests\portal;

use go1\clients\UserClient;
use go1\util\portal\PortalHelper;
use go1\util\schema\mock\InstanceMockTrait;
use go1\util\tests\UtilTestCase;

class PortalHelperTest extends UtilTestCase
{
    use InstanceMockTrait;

    private $portalName = 'foo.com';

    public function testLogo()
    {
        $instanceId = $this->createInstance($this->db, ['data' => ['files' => ['logo' => 'http://www.go1.com/logo.png']]]);
        $portal = PortalHelper::load($this->db, $instanceId);
        $logo = PortalHelper::logo($portal);
        $this->assertEquals('http://www.go1.com/logo.png', $logo);

        $instanceId = $this->createInstance($this->db, ['data' => ['files' => ['logo' => '//www.go1.com/logo.png']]]);
        $portal = PortalHelper::load($this->db, $instanceId);
        $logo = PortalHelper::logo($portal);
        $this->assertEquals('https://www.go1.com/logo.png', $logo);

        $instanceId = $this->createInstance($this->db, []);
        $portal = PortalHelper::load($this->db, $instanceId);
        $logo = PortalHelper::logo($portal);
        $this->assertEmpty($logo);
    }

    public function testRoles()
    {
        $id = $this->createPortalAdminRole($this->db, ['instance' => $portalName = 'abc.go1.co']);
        $roles = PortalHelper::roles($this->db, $portalName);
        $this->assertCount(1, $roles);
        $this->assertEquals($roles[$id], 'administrator');
    }

    public function testPortalAdminIds()
    {
        $admin1Id = $this->createUser($this->db, [
            'instance' => $this->portalName,
            'mail'     => 'a1@mail.com',
        ]);
        $admin2Id = $this->createUser($this->db, [
            'instance' => $this->portalName,
            'mail'     => 'a2@mail.com',
        ]);
        $this->createUser($this->db, [
            'instance' => $this->portalName,
            'mail'     => 'a3@mail.com',
        ]);
        $adminIds = [$admin1Id, $admin2Id];

        $app = $this->getContainer();
        $app->extend('go1.client.user', function () use ($adminIds) {
            $userClient = $this->getMockBuilder(UserClient::class)
                ->disableOriginalConstructor()
                ->setMethods(['findAdministrators'])
                ->getMock();
            $userClient
                ->expects($this->any())
                ->method('findAdministrators')
                ->willReturnCallback(function () use ($adminIds) {
                    foreach ($adminIds as $adminId) {
                        yield (object)['id' => $adminId];
                    }
                });

            return $userClient;
        });

        $userClient = $app['go1.client.user'];
        $admins = PortalHelper::portalAdminIds($userClient, $this->portalName);
        $this->assertEquals(2, count($admins));
        $this->assertEquals($admin1Id, $admins[0]);
        $this->assertEquals($admin2Id, $admins[1]);

        return [$userClient];
    }

    /** @depends testPortalAdminIds */
    public function testPortalAdmins(array $params)
    {
        list($userClient) = $params;
        $admin1Id = $this->createUser($this->db, ['instance' => $this->portalName, 'mail' => 'a1@mail.com']);
        $admin2Id = $this->createUser($this->db, ['instance' => $this->portalName, 'mail' => 'a2@mail.com']);

        $admins = PortalHelper::portalAdmins($this->db, $userClient, $this->portalName);

        $this->assertEquals(2, count($admins));
        $this->assertEquals($admin1Id, $admins[0]->id);
        $this->assertEquals($admin2Id, $admins[1]->id);
    }
}
