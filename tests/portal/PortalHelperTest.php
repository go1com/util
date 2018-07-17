<?php

namespace go1\util\tests\portal;

use go1\clients\UserClient;
use go1\util\collection\PortalCollectionConfiguration;
use go1\util\model\Portal;
use go1\util\portal\PortalHelper;
use go1\util\schema\mock\PortalMockTrait;
use go1\util\tests\UtilTestCase;

class PortalHelperTest extends UtilTestCase
{
    use PortalMockTrait;

    private $portalName = 'foo.com';

    public function testLogo()
    {
        $instanceId = $this->createPortal($this->db, ['data' => ['files' => ['logo' => 'http://www.go1.com/logo.png']]]);
        $portal = PortalHelper::load($this->db, $instanceId);
        $logo = PortalHelper::logo($portal);
        $this->assertEquals('http://www.go1.com/logo.png', $logo);

        $instanceId = $this->createPortal($this->db, ['data' => ['files' => ['logo' => '//www.go1.com/logo.png']]]);
        $portal = PortalHelper::load($this->db, $instanceId);
        $logo = PortalHelper::logo($portal);
        $this->assertEquals('https://www.go1.com/logo.png', $logo);

        $instanceId = $this->createPortal($this->db, []);
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
            $userClient = $this
                ->getMockBuilder(UserClient::class)
                ->disableOriginalConstructor()
                ->setMethods(['findAdministrators'])
                ->getMock();

            $userClient
                ->expects($this->any())
                ->method('findAdministrators')
                ->willReturnCallback(function () use ($adminIds) {
                    foreach ($adminIds as $adminId) {
                        yield (object) ['id' => $adminId];
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

    public function testLanguage()
    {
        $portalEnglish = (object) ['data' => (object) ['configuration' => (object) [PortalHelper::LANGUAGE => PortalHelper::LANGUAGE_DEFAULT]]];
        $this->assertEquals(PortalHelper::LANGUAGE_DEFAULT, PortalHelper::language($portalEnglish));

        $portalCatalan = (object) ['data' => (object) ['configuration' => (object) [PortalHelper::LANGUAGE => 'ca']]];
        $this->assertEquals('ca', PortalHelper::language($portalCatalan));
    }

    public function testTimeZone()
    {
        $instanceId = $this->createPortal($this->db, ['title' => 'qa.mygo1.com', 'data' => ['configuration' => ['timezone' => "Australia/Canberra"]]]);

        $portal = PortalHelper::load($this->db, $instanceId);
        $this->assertEquals("Australia/Canberra", PortalHelper::timezone($portal));
    }

    public function testLocale()
    {
        $instanceId = $this->createPortal($this->db, ['title' => 'qa.mygo1.com', 'data' => ['configuration' => ['locale' => "AU"]]]);

        $portal = PortalHelper::load($this->db, $instanceId);
        $this->assertEquals("AU", PortalHelper::locale($portal));
    }

    public function testCollections()
    {
        $portalId = $this->createPortal($this->db, ['title' => 'qa.mygo1.com']);
        $portal = PortalHelper::load($this->db, $portalId);
        $collections = PortalHelper::collections($portal);
        $this->assertEquals(PortalHelper::COLLECTIONS_DEFAULT, $collections);

        $portalId = $this->createPortal($this->db, ['title' => 'test.mygo1.com', 'data' => ['configuration' => ['collections' => [PortalCollectionConfiguration::SUBSCRIBE]]]]);
        $portal = PortalHelper::load($this->db, $portalId);
        $collections = PortalHelper::collections($portal);
        $this->assertEquals([PortalCollectionConfiguration::SUBSCRIBE], $collections);

        $portalId = $this->createPortal($this->db, ['title' => 'test2.mygo1.com', 'data' => ['configuration' => ['collections' => []]]]);
        $portal = PortalHelper::load($this->db, $portalId);
        $collections = PortalHelper::collections($portal);
        $this->assertEquals([], $collections);
    }

    public function testPortalData()
    {
        $portalId = $this->createPortal($this->db, ['title' => 'qa.mygo1.com']);
        $this->createPortalData($this->db, ['id' => $portalId]);
        $portalData = PortalHelper::loadPortalDataById($this->db, $portalId);
        $this->assertEquals($portalId, $portalData->id);
    }
}
