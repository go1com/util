<?php

namespace go1\util\tests\portal;

use go1\util\portal\PortalChecker;
use go1\util\portal\PortalHelper;
use go1\util\schema\mock\PortalMockTrait;
use go1\util\tests\UtilTestCase;
use go1\util\user\Roles;

class PortalCheckerTest extends UtilTestCase
{
    use PortalMockTrait;

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
            ['qa', 'qa.mygo1.com', '', '', 'https://qa.mygo1.com/p/#/'],
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

    public function testAllowDiscussion()
    {
        $id = $this->createInstance($this->db, [
            'data' => ['configuration' => ['discussion' => 0]],
        ]);
        $portal = PortalHelper::load($this->db, $id);
        $this->assertFalse(PortalChecker::allowDiscussion($portal));

        $id = $this->createInstance($this->db, [
            'data' => ['configuration' => ['discussion' => 1]],
        ]);
        $portal = PortalHelper::load($this->db, $id);
        $this->assertTrue(PortalChecker::allowDiscussion($portal));

        $id = $this->createInstance($this->db, [
            'data' => ['configuration' => ['discussionEnabled' => 0]],
        ]);
        $portal = PortalHelper::load($this->db, $id);
        $this->assertFalse(PortalChecker::allowDiscussion($portal));

        $id = $this->createInstance($this->db, []);
        $portal = PortalHelper::load($this->db, $id);
        $this->assertTrue(PortalChecker::allowDiscussion($portal));
    }

    public function testAllowUserInvite()
    {
        $id = $this->createInstance($this->db, [
            'data' => ['configuration' => ['user_invite' => 0]],
        ]);
        $portal = PortalHelper::load($this->db, $id);
        $this->assertFalse(PortalChecker::allowUserInvite($portal));

        $id = $this->createInstance($this->db, [
            'data' => ['configuration' => ['user_invite' => 1]],
        ]);
        $portal = PortalHelper::load($this->db, $id);
        $this->assertTrue(PortalChecker::allowUserInvite($portal));

        $id = $this->createInstance($this->db, []);
        $portal = PortalHelper::load($this->db, $id);
        $this->assertTrue(PortalChecker::allowUserInvite($portal));
    }

    public function testAllowPublicProfile()
    {
        $id = $this->createInstance($this->db, [
            'data' => ['configuration' => ['public_profiles' => 0]],
        ]);
        $portal = PortalHelper::load($this->db, $id);
        $this->assertFalse(PortalChecker::allowPublicProfile($portal));

        $id = $this->createInstance($this->db, [
            'data' => ['configuration' => ['public_profiles' => 1]],
        ]);
        $portal = PortalHelper::load($this->db, $id);
        $this->assertTrue(PortalChecker::allowPublicProfile($portal));

        $id = $this->createInstance($this->db, []);
        $portal = PortalHelper::load($this->db, $id);
        $this->assertFalse(PortalChecker::allowPublicProfile($portal));
    }

    public function testAllowUserPayment()
    {
        $id = $this->createInstance($this->db, [
            'data' => ['configuration' => ['user_payment' => 0]],
        ]);
        $portal = PortalHelper::load($this->db, $id);
        $this->assertFalse(PortalChecker::allowUserPayment($portal));

        $id = $this->createInstance($this->db, [
            'data' => ['configuration' => ['user_payment' => 1]],
        ]);
        $portal = PortalHelper::load($this->db, $id);
        $this->assertTrue(PortalChecker::allowUserPayment($portal));

        $id = $this->createInstance($this->db, []);
        $portal = PortalHelper::load($this->db, $id);
        $this->assertTrue(PortalChecker::allowUserPayment($portal));
    }

    public function testAllowMarketplace()
    {
        $id = $this->createInstance($this->db, [
            'data' => ['features' => ['marketplace' => 0]],
        ]);
        $portal = PortalHelper::load($this->db, $id);
        $this->assertFalse(PortalChecker::allowMarketplace($portal));

        $id = $this->createInstance($this->db, [
            'data' => ['features' => ['marketplace' => 1]],
        ]);
        $portal = PortalHelper::load($this->db, $id);
        $this->assertTrue(PortalChecker::allowMarketplace($portal));

        $id = $this->createInstance($this->db, []);
        $portal = PortalHelper::load($this->db, $id);
        $this->assertTrue(PortalChecker::allowMarketplace($portal));
    }


    public function notifyRemindConfig()
    {
        return [
            [
                [Roles::STUDENT => 1, Roles::ASSESSOR => 1, Roles::MANAGER => 1],
                [Roles::STUDENT => true, Roles::ASSESSOR => true, Roles::MANAGER => true, Roles::ADMIN => false]
            ],
            [
                [Roles::STUDENT => 0, Roles::ASSESSOR => 0, Roles::MANAGER => 0],
                [Roles::STUDENT => false, Roles::ASSESSOR => false, Roles::MANAGER => false]
            ],
            [
                [],
                [Roles::STUDENT => false, Roles::ASSESSOR => false, Roles::MANAGER => false]
            ],
            [
                [Roles::STUDENT => 1],
                [Roles::STUDENT => true, Roles::ASSESSOR => false, Roles::MANAGER => false]
            ],
            [
                [Roles::ASSESSOR => 1],
                [Roles::STUDENT => false, Roles::ASSESSOR => true, Roles::MANAGER => false]
            ],
            [
                [Roles::MANAGER => 1],
                [Roles::STUDENT => false, Roles::ASSESSOR => false, Roles::MANAGER => true]
            ],
        ];
    }

    /**
     * @dataProvider notifyRemindConfig
     */
    public function testAllowNotifyRemindMajorEventByRole($data, $expected)
    {
        $id = $this->createInstance($this->db, [
            'data' => ['configuration' => [PortalHelper::FEATURE_NOTIFY_REMIND_MAJOR_EVENT => $data]],
        ]);
        $portal = PortalHelper::load($this->db, $id);
        foreach ($expected as $role => $assert) {
            $this->assertEquals($assert, PortalChecker::allowNotifyRemindMajorEventByRole($portal, $role));
        }
    }
}
