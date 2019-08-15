<?php

namespace go1\util\tests\portal;

use go1\clients\UserClient;
use go1\util\collection\PortalCollectionConfiguration;
use go1\util\portal\PortalHelper;
use go1\util\schema\mock\PortalMockTrait;
use go1\util\tests\UtilCoreTestCase;

class PortalHelperTest extends UtilCoreTestCase
{
    use PortalMockTrait;

    private $portalName = 'foo.com';

    public function testLogo()
    {
        $portalId = $this->createPortal($this->go1, ['data' => ['files' => ['logo' => 'http://www.go1.com/logo.png']]]);
        $portal = PortalHelper::load($this->go1, $portalId);
        $logo = PortalHelper::logo($portal);
        $this->assertEquals('http://www.go1.com/logo.png', $logo);

        $portalId = $this->createPortal($this->go1, ['data' => ['files' => ['logo' => '//www.go1.com/logo.png']]]);
        $portal = PortalHelper::load($this->go1, $portalId);
        $logo = PortalHelper::logo($portal);
        $this->assertEquals('https://www.go1.com/logo.png', $logo);

        $portalId = $this->createPortal($this->go1, []);
        $portal = PortalHelper::load($this->go1, $portalId);
        $logo = PortalHelper::logo($portal);
        $this->assertEmpty($logo);
    }

    public function testRoles()
    {
        $id = $this->createPortalAdminRole($this->go1, ['instance' => $portalName = 'abc.go1.co']);
        $roles = PortalHelper::roles($this->go1, $portalName);
        $this->assertCount(1, $roles);
        $this->assertEquals($roles[$id], 'administrator');
    }

    public function testPortalAdminIds()
    {
        $admin1Id = $this->createUser($this->go1, ['instance' => $this->portalName, 'mail' => 'a1@mail.com']);
        $admin2Id = $this->createUser($this->go1, ['instance' => $this->portalName, 'mail' => 'a2@mail.com']);
        $this->createUser($this->go1, ['instance' => $this->portalName, 'mail' => 'a3@mail.com']);
        $adminIds = [$admin1Id, $admin2Id];

        $app = $this->getContainer();
        $app['go1.client.user'] = function () use ($adminIds) {
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
        };

        /** @var UserClient $userClient */
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
        $admin1Id = $this->createUser($this->go1, ['instance' => $this->portalName, 'mail' => 'a1@mail.com']);
        $admin2Id = $this->createUser($this->go1, ['instance' => $this->portalName, 'mail' => 'a2@mail.com']);

        $admins = PortalHelper::portalAdmins($this->go1, $userClient, $this->portalName);

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
        $instanceId = $this->createPortal($this->go1, ['title' => 'qa.mygo1.com', 'data' => ['configuration' => ['timezone' => "Australia/Canberra"]]]);

        $portal = PortalHelper::load($this->go1, $instanceId);
        $this->assertEquals("Australia/Canberra", PortalHelper::timezone($portal));
    }

    public function testLocale()
    {
        $instanceId = $this->createPortal($this->go1, ['title' => 'qa.mygo1.com', 'data' => ['configuration' => ['locale' => "AU"]]]);

        $portal = PortalHelper::load($this->go1, $instanceId);
        $this->assertEquals("AU", PortalHelper::locale($portal));
    }

    public function testCollections()
    {
        $portalId = $this->createPortal($this->go1, ['title' => 'qa.mygo1.com']);
        $portal = PortalHelper::load($this->go1, $portalId);
        $collections = PortalHelper::collections($portal);
        $this->assertEquals(PortalHelper::COLLECTIONS_DEFAULT, $collections);

        $portalId = $this->createPortal($this->go1, ['title' => 'test.mygo1.com', 'data' => ['configuration' => ['collections' => [PortalCollectionConfiguration::SUBSCRIBE]]]]);
        $portal = PortalHelper::load($this->go1, $portalId);
        $collections = PortalHelper::collections($portal);
        $this->assertEquals([PortalCollectionConfiguration::SUBSCRIBE], $collections);

        $portalId = $this->createPortal($this->go1, ['title' => 'test2.mygo1.com', 'data' => ['configuration' => ['collections' => []]]]);
        $portal = PortalHelper::load($this->go1, $portalId);
        $collections = PortalHelper::collections($portal);
        $this->assertEquals([], $collections);
    }

    public function testPortalData()
    {
        $portalId = $this->createPortal($this->go1, ['title' => 'qa.mygo1.com']);
        $this->createPortalData($this->go1, ['id' => $portalId]);
        $portalData = PortalHelper::loadPortalDataById($this->go1, $portalId);
        $this->assertEquals($portalId, $portalData->id);
    }

    public function testPortalDataIncludesDataOptionally()
    {
        $customerID = 'customer';
        $portalId = $this->createPortal($this->go1, ['title' => 'qa.mygo1.com']);
        $this->createPortalData($this->go1, ['id' => $portalId, 'customer_id' => $customerID]);
        $portalData = PortalHelper::load($this->go1, $portalId, '*', false, true);
        $this->assertEquals($portalId, $portalData->id);
        $this->assertEquals($customerID, $portalData->data->portal_data->customer_id);
    }

    public function testDNSCheck() {
        $result = PortalHelper::validateCustomDomainDNS(PortalHelper::CUSTOM_DOMAIN_DEFAULT_HOST);
        $this->assertEquals($result, true);
    }
}
