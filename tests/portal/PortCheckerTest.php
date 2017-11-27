<?php

namespace go1\util\tests\portal;

use go1\util\portal\PortalHelper;
use go1\util\schema\mock\InstanceMockTrait;
use go1\util\tests\UtilTestCase;
use go1\util\portal\PortalChecker;

class PortalCheckerTest extends UtilTestCase
{
    use InstanceMockTrait;

    public function testAllowPublicGroup()
    {
        $instanceId = $this->createInstance($this->db, ['title' => 'qa.mygo1.com']);
        $portal = PortalHelper::load($this->db, $instanceId);
        $portalChecker = new PortalChecker();
        $group = $portalChecker->allowPublicGroup($portal);
        $this->assertFalse($group);
    }
}
