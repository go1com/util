<?php

namespace go1\util\tests;

use go1\util\AccessChecker;
use go1\util\edge\EdgeHelper;
use go1\util\edge\EdgeTypes;
use go1\util\group\GroupHelper;
use go1\util\group\GroupItemStatus;
use go1\util\group\GroupItemTypes;
use go1\util\schema\mock\GroupMockTrait;
use go1\util\schema\mock\InstanceMockTrait;
use go1\util\schema\mock\LoMockTrait;
use go1\util\schema\mock\NoteMockTrait;
use go1\util\schema\mock\UserMockTrait;
use go1\util\user\Roles;
use Symfony\Component\HttpFoundation\Request;

class GroupHelperTest extends UtilTestCase
{
    use GroupMockTrait;
    use UserMockTrait;
    use InstanceMockTrait;
    use LoMockTrait;
    use NoteMockTrait;

    public function setUp()
    {
        parent::setUp();
        $this->installGo1Schema($this->db, $coreOnly = false);
    }

    public function testIsItemOf()
    {
        $groupId = $this->createGroup($this->db);
        $this->createGroupItem($this->db, ['group_id' => $groupId, 'entity_type' => 'lo', 'entity_id' => 100]);
        $this->assertTrue(GroupHelper::isItemOf($this->db, 'lo', 100, $groupId));
        $this->assertFalse(GroupHelper::isItemOf($this->db, 'lo', 1001, $groupId));
    }

    public function testCanAccess()
    {
        $groupId = $this->createGroup($this->db);
        $this->createGroupItem($this->db, ['group_id' => $groupId, 'entity_type' => 'user', 'entity_id' => $user1Id = 25]);
        $this->assertTrue(GroupHelper::canAccess($this->db, $user1Id, $groupId));

        $this->createGroupItem($this->db, ['group_id' => $groupId, 'entity_type' => 'user', 'entity_id' => $user2Id = 52, 'status' => GroupItemStatus::PENDING]);
        $this->assertFalse(GroupHelper::canAccess($this->db, $user2Id, $groupId));
        $this->assertFalse(GroupHelper::canAccess($this->db, $userId = 404, $groupId));
    }

    public function testGroupAccess()
    {
        $userId = 1000;
        $groupId = $this->createGroup($this->db, ['user_id' => $userId]);
        $group = GroupHelper::load($this->db, $groupId);
        $groupUserId = $group->user_id;
        $this->assertTrue(GroupHelper::groupAccess($groupUserId, $userId));

        $accessChecker = new AccessChecker();
        $req = new Request();
        $req->request->replace(['jwt.payload' => $this->getJwt(null, null, null, Roles::ACCOUNTS_ROLES)], null, null, false);
        $this->assertTrue(GroupHelper::groupAccess($groupUserId, $userId, $accessChecker, $req));

        $instance = 'site.mygo1.com';
        $req->request->replace(['jwt.payload' => $this->getJwt(null, null, $instance, [Roles::ADMIN]), null, null, false]);
        $this->assertTrue(GroupHelper::groupAccess($groupUserId, $userId, $accessChecker, $req, $instance));
    }

    public function testGetAccountId()
    {
        $instance = 'az.mygo1.com';
        $userId = $this->createUser($this->db, ['mail' => 'user@go1.com', 'instance' => $instance]);
        $this->assertEquals($userId, GroupHelper::getAccountId($this->db, ['mail' => 'user@go1.com'], $instance));
        $this->assertEquals(0, GroupHelper::getAccountId($this->db, ['mail' => 'user@go1.com'], 'other.mygo1.com'));
    }

    public function testGetEntityIdPortal()
    {
        $instanceId = $this->createInstance($this->db, []);
        $entityId = GroupHelper::getEntityId($this->db, $this->db, $this->db, 'portal', $instanceId);

        $this->assertEquals($instanceId, $entityId);
    }

    public function testGetEntityIdUser()
    {
        $userId = $this->createUser($this->db, ['mail' => 'user@go1.com', 'instance' => 'accounts']);
        $accountId = $this->createUser($this->db, ['mail' => 'user@go1.com']);
        EdgeHelper::link($this->db, $this->queue, EdgeTypes::HAS_ACCOUNT, $userId, $accountId);

        $entityId = GroupHelper::getEntityId($this->db, $this->db, $this->db, 'user', $userId, 'az.mygo1.com');

        $this->assertEquals($accountId, $entityId);
    }

    public function testGetEntityIdLo()
    {
        $loId = $this->createCourse($this->db, []);

        $entityId = GroupHelper::getEntityId($this->db, $this->db, $this->db, 'lo', $loId);

        $this->assertEquals($loId, $entityId);
    }

    public function testGetEntityIdNote()
    {
        $noteId = $this->createNote($this->db, []);

        $entityId = GroupHelper::getEntityId($this->db, $this->db, $this->db, 'note', 'NOTE_UUID');

        $this->assertEquals($noteId, $entityId);
    }

    public function testGetEntityIdGroup()
    {
        $groupId = $this->createGroup($this->db, []);

        $entityId = GroupHelper::getEntityId($this->db, $this->db, $this->db, 'group', $groupId);

        $this->assertEquals($groupId, $entityId);
    }

    public function testIsPremium()
    {
        $groupPremiumId = $this->createGroup($this->db, ['data' => ['premium' => 1]]);
        $groupPremium = GroupHelper::load($this->db, $groupPremiumId);

        $this->assertTrue(GroupHelper::isPremium($groupPremium));

        $groupId1 = $this->createGroup($this->db, ['data' => ['premium' => 0]]);
        $group1 = GroupHelper::load($this->db, $groupId1);

        $this->assertFalse(GroupHelper::isPremium($group1));

        $groupId2 = $this->createGroup($this->db, []);
        $group2 = GroupHelper::load($this->db, $groupId2);

        $this->assertFalse(GroupHelper::isPremium($group2));
    }

    public function testIsMarketplace()
    {
        $groupMarketId = $this->createGroup($this->db, ['data' => ['marketplace' => 1]]);
        $groupMarket = GroupHelper::load($this->db, $groupMarketId);

        $this->assertTrue(GroupHelper::isMarketplace($groupMarket));

        $groupId1 = $this->createGroup($this->db, ['data' => ['marketplace' => 0]]);
        $group1 = GroupHelper::load($this->db, $groupId1);

        $this->assertFalse(GroupHelper::isMarketplace($group1));

        $groupId2 = $this->createGroup($this->db, []);
        $group2 = GroupHelper::load($this->db, $groupId2);

        $this->assertFalse(GroupHelper::isMarketplace($group2));
    }

    public function testFindItems()
    {
        $fooGroupId = 1;
        $barGroupId = 2;
        $this->createGroupItem($this->db, ['group_id' => $fooGroupId, 'entity_type' => GroupItemTypes::LO, 'entity_id' => 1]);
        $this->createGroupItem($this->db, ['group_id' => $fooGroupId, 'entity_type' => GroupItemTypes::LO, 'entity_id' => 2]);
        $this->createGroupItem($this->db, ['group_id' => $barGroupId, 'entity_type' => GroupItemTypes::LO, 'entity_id' => 3]);
        $this->createGroupItem($this->db, ['group_id' => $fooGroupId, 'entity_type' => GroupItemTypes::USER, 'entity_id' => 1]);
        $this->createGroupItem($this->db, ['group_id' => $barGroupId, 'entity_type' => GroupItemTypes::USER, 'entity_id' => 2]);

        $items = [];
        foreach (GroupHelper::findItems($this->db, $fooGroupId, GroupItemTypes::LO, 1, 0, true) as $item) {
            $items[] = $item;
        }
        $this->assertCount(2, $items);

        $items = [];
        foreach (GroupHelper::findItems($this->db, $fooGroupId, null) as $item) {
            $items[] = $item;
        }
        $this->assertCount(3, $items);

        $items = [];
        foreach (GroupHelper::findItems($this->db, $fooGroupId, GroupItemTypes::USER) as $item) {
            $items[] = $item;
        }
        $this->assertCount(1, $items);

        $items = [];
        foreach (GroupHelper::findItems($this->db, $barGroupId, null, 1) as $item) {
            $items[] = $item;
        }
        $this->assertCount(1, $items);
    }
}
