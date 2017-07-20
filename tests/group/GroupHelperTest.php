<?php

namespace go1\util\tests\group;

use go1\util\AccessChecker;
use go1\util\edge\EdgeHelper;
use go1\util\edge\EdgeTypes;
use go1\util\group\GroupAssignStatuses;
use go1\util\group\GroupAssignTypes;
use go1\util\group\GroupHelper;
use go1\util\group\GroupItemStatus;
use go1\util\group\GroupItemTypes;
use go1\util\schema\mock\GroupMockTrait;
use go1\util\schema\mock\InstanceMockTrait;
use go1\util\schema\mock\LoMockTrait;
use go1\util\schema\mock\NoteMockTrait;
use go1\util\schema\mock\UserMockTrait;
use go1\util\tests\UtilTestCase;
use go1\util\user\Roles;
use Symfony\Component\HttpFoundation\Request;

class GroupHelperTest extends UtilTestCase
{
    use GroupMockTrait;
    use UserMockTrait;
    use InstanceMockTrait;
    use LoMockTrait;
    use NoteMockTrait;

    public function testInstanceId()
    {
        $groupId = $this->createGroup($this->db, ['instance_id' => 555]);
        $this->assertEquals(555, GroupHelper::instanceId($this->db, $groupId));
        $this->assertEquals(null, GroupHelper::instanceId($this->db, $groupId + 666));
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
        $c = $this->getContainer();
        $portalId = $this->createInstance($this->db, ['title' => $portalName = 'foo.com']);
        $fooUserId = $this->createUser($this->db, ['id' => 31, 'instance' => $c['accounts_name']]);
        $barUserId = $this->createUser($this->db, [
            'id'       => 33,
            'mail'     => $barMail = 'bar@foo.com',
            'instance' => $c['accounts_name'],
        ]);
        $barAccountId = $this->createUser($this->db, [
            'id'       => 34,
            'mail'     => $barMail,
            'instance' => $portalName,
        ]);
        $groupId = $this->createGroup($this->db, ['user_id' => $fooUserId, 'instance_id' => $portalId]);
        $this->createGroupItem($this->db, ['group_id' => $groupId, 'entity_type' => 'user', 'entity_id' => $barAccountId]);

        $this->assertTrue(GroupHelper::canAccess($this->db, $this->db, $fooUserId, $groupId));
        $this->assertTrue(GroupHelper::canAccess($this->db, $this->db, $barUserId, $groupId));
    }

    public function testCantAccess()
    {
        $c = $this->getContainer();
        $portalId = $this->createInstance($this->db, ['title' => $portalName = 'foo.com']);
        $fooUserId = $this->createUser($this->db, [
            'id'       => 33,
            'mail'     => $fooMail = 'foo@foo.com',
            'instance' => $c['accounts_name'],
        ]);
        $fooAccountId = $this->createUser($this->db, [
            'id'       => 34,
            'mail'     => $fooMail,
            'instance' => $portalName,
        ]);
        $groupId = $this->createGroup($this->db, [
            'instance_id' => $portalId,
        ]);
        $this->createGroupItem($this->db, [
            'group_id'    => $groupId,
            'entity_type' => 'user',
            'entity_id'   => $fooAccountId,
            'status'      => GroupItemStatus::PENDING,
        ]);

        $this->assertFalse(GroupHelper::canAccess($this->db, $this->db, $fooUserId, $groupId));
        $this->assertFalse(GroupHelper::canAccess($this->db, $this->db, $barUserId = 404, $groupId));
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

    public function testFormat()
    {
        $groupId = $this->createGroup($this->db, ['data' => ['marketplace' => 1, 'description' => 'group description']]);
        $group = GroupHelper::load($this->db, $groupId);

        $this->assertTrue(GroupHelper::isMarketplace($group));
        $this->assertEquals('group description', $group->description);
    }

    public function testCountMembers()
    {
        $groupId = $this->createGroup($this->db, ['instance_id' => 555]);

        $i = 1;
        while ($i < 11) {
            $this->createGroupItem($this->db, ['group_id' => $groupId, 'entity_id' => $i]);
            $i++;
        }

        $group = GroupHelper::load($this->db, $groupId);
        $this->assertEquals(10, $group->member_count);

        $groupId2 = $this->createGroup($this->db, ['instance_id' => 555]);

        $i = 1;
        while ($i < 21) {
            $this->createGroupItem($this->db, ['group_id' => $groupId2, 'entity_id' => $i]);
            $i++;
        }

        $group = GroupHelper::load($this->db, $groupId2);
        $this->assertEquals(20, $group->member_count);
    }

    public function testLoadAssignsByGroup()
    {
        $groupId = 1;
        $this->createGroupAssign($this->db, [
            'group_id'    => $groupId,
            'instance_id' => 1,
            'entity_type' => GroupAssignTypes::LO,
            'entity_id'   => 33,
            'user_id'     => 99,
            'status'      => GroupAssignStatuses::PUBLISHED,
        ]);
        $this->createGroupAssign($this->db, [
            'group_id'    => $groupId,
            'instance_id' => 1,
            'entity_type' => GroupAssignTypes::LO,
            'entity_id'   => 34,
            'user_id'     => 99,
            'status'      => GroupAssignStatuses::PUBLISHED,
        ]);
        $this->createGroupAssign($this->db, [
            'group_id'    => $groupId,
            'instance_id' => 1,
            'entity_type' => GroupAssignTypes::LO,
            'entity_id'   => 35,
            'user_id'     => 99,
            'status'      => GroupAssignStatuses::ARCHIVED,
        ]);

        $this->assertCount(2, GroupHelper::groupAssigns($this->db, $groupId));
        $this->assertCount(1, GroupHelper::groupAssigns($this->db, $groupId, ['status' => GroupAssignStatuses::ARCHIVED]));
    }
}
