<?php

namespace go1\util\tests\portal;

use go1\util\portal\PortalHelper;
use go1\util\schema\mock\InstanceMockTrait;
use go1\util\tests\UtilTestCase;
use go1\util\portal\PortalChecker;

class PortalCheckerTest extends UtilTestCase
{
    use InstanceMockTrait;

    public function testAllowPublicGroupFalse()
    {
        $instanceId = $this->createInstance($this->db, [
            'title' => 'qa.mygo1.com',
            'data'  => json_encode([
                'configuration' => ['public_group' => 0]
            ])
        ]);

        $portal = PortalHelper::load($this->db, $instanceId);

        $portalChecker = new PortalChecker();
        $group = $portalChecker->allowPublicGroup($portal);

        $this->assertFalse($group);
    }

    public function testAllowPublicGroupTrue()
    {
        $instanceId = $this->createInstance($this->db, [
            'title' => 'qa.mygo1.com',
            'data'  => json_encode([
                'configuration' => ['public_group' => 1]
            ])
        ]);

        $portal = PortalHelper::load($this->db, $instanceId);
        $portalChecker = new PortalChecker();
        $group = $portalChecker->allowPublicGroup($portal);

        $this->assertTrue($group);
    }

    public function testAllowPublicGroupTrueWithoutFieldPublicGroup()
    {
        $instanceId = $this->createInstance($this->db, [
            'title' => 'qa.mygo1.com'
        ]);

        $portal = PortalHelper::load($this->db, $instanceId);
        $portalChecker = new PortalChecker();
        $group = $portalChecker->allowPublicGroup($portal);

        $this->assertFalse($group);
    }

    public function testAllowPublicGroupEnableTrue()
    {
        $instanceId = $this->createInstance($this->db, [
            'title' => 'qa.mygo1.com',
            'data'  => json_encode([
                'configuration' => ['publicGroupsEnabled' => 1]
            ])
        ]);

        $portal = PortalHelper::load($this->db, $instanceId);
        $portalChecker = new PortalChecker();
        $group = $portalChecker->allowPublicGroup($portal);

        $this->assertTrue($group);
    }

    public function testAllowPublicGroupEnableFalse()
    {
        $instanceId = $this->createInstance($this->db, [
            'title' => 'qa.mygo1.com',
            'data'  => json_encode([
                'configuration' => ['publicGroupsEnabled' => 0]
            ])
        ]);

        $portal = PortalHelper::load($this->db, $instanceId);
        $portalChecker = new PortalChecker();
        $group = $portalChecker->allowPublicGroup($portal);

        $this->assertFalse($group);
    }
}
