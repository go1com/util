<?php

namespace go1\util\tests\portal;

use go1\util\portal\PortalHelper;
use go1\util\portal\PortalPricing;
use go1\util\schema\mock\InstanceMockTrait;
use go1\util\tests\UtilTestCase;

class PortalHelperTest extends UtilTestCase
{
    use InstanceMockTrait;

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
    }

    public function testRoles()
    {
        $id = $this->createPortalAdminRole($this->db, ['instance' => $portalName = 'abc.go1.co']);
        $roles = PortalHelper::roles($this->db, $portalName);
        $this->assertCount(1, $roles);
        $this->assertEquals($roles[$id], 'administrator');
    }
}
