<?php

namespace go1\util\tests\portal;

use go1\util\portal\PortalChecker;
use go1\util\portal\PortalHelper;
use go1\util\schema\mock\InstanceMockTrait;
use go1\util\tests\UtilTestCase;

class PortalCheckerTest extends UtilTestCase
{
    use InstanceMockTrait;

    public function testAllowPublicGroupFalse()
    {
        $instanceId = $this->createInstance($this->db, [
            'title' => 'qa.mygo1.com',
            'data'  => json_encode([
                'configuration' => ['public_group' => 0],
            ]),
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
                'configuration' => ['public_group' => 1],
            ]),
        ]);

        $portal = PortalHelper::load($this->db, $instanceId);
        $portalChecker = new PortalChecker();
        $group = $portalChecker->allowPublicGroup($portal);

        $this->assertTrue($group);
    }

    public function testAllowPublicGroupTrueWithoutFieldPublicGroup()
    {
        $instanceId = $this->createInstance($this->db, [
            'title' => 'qa.mygo1.com',
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
                'configuration' => ['publicGroupsEnabled' => 1],
            ]),
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
                'configuration' => ['publicGroupsEnabled' => 0],
            ]),
        ]);

        $portal = PortalHelper::load($this->db, $instanceId);
        $portalChecker = new PortalChecker();
        $group = $portalChecker->allowPublicGroup($portal);

        $this->assertFalse($group);
    }

    public function dataBuildLink()
    {
        return [
            ['production', 'az.mygo1.com', '', '', 'https://az.mygo1.com/p/#/'],
            ['production', 'az.mygo1.com', '/', '', 'https://az.mygo1.com/p/#/'],
            ['production', 'public.mygo1.com', '', '', 'https://www.go1.com/'],
            ['production', 'az.mygo1.com', 'embed-course/12345/', 'embed.html', 'https://az.mygo1.com/p/embed.html#/embed-course/12345/'],
            ['staging', 'staging.mygo1.com', '', '', 'https://staging.mygo1.com/p/#/'],
            ['dev', 'dev.mygo1.com', '', '', 'https://dev.mygo1.com/p/#/'],
            ['', 'dev.mygo1.com', '', '', 'https://dev.mygo1.com/p/#/'],
        ];
    }

    /**
     * @dataProvider dataBuildLink
     */
    public function testBuildLink(string $env, string $instance, string $uri, string $prefix, string $expecting)
    {
        putenv("ENV=$env");
        $instanceId = $this->createInstance($this->db, ['title' => $instance]);
        $portal = PortalHelper::load($this->db, $instanceId);

        $this->assertEquals($expecting, (new PortalChecker)->buildLink($portal, $uri, $prefix));
    }
}
