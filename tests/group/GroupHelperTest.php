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
use go1\util\group\GroupRepository;
use go1\util\group\GroupStatus;
use go1\util\group\GroupTypes;
use go1\util\schema\mock\AwardMockTrait;
use go1\util\schema\mock\GroupMockTrait;
use go1\util\schema\mock\PortalMockTrait;
use go1\util\schema\mock\LoMockTrait;
use go1\util\schema\mock\NoteMockTrait;
use go1\util\schema\mock\UserMockTrait;
use go1\util\tests\UtilTestCase;
use go1\util\user\Roles;
use go1\util\user\UserHelper;
use Symfony\Component\HttpFoundation\Request;

class GroupHelperTest extends UtilTestCase
{
    use GroupMockTrait;
    use UserMockTrait;
    use PortalMockTrait;
    use LoMockTrait;
    use NoteMockTrait;
    use AwardMockTrait;

    /** @var  GroupRepository */
    private $repository;

    public function setUp()
    {
        parent::setUp();

        $this->repository = new GroupRepository($this->db, $this->queue);
    }

    public function testInstanceId()
    {
        $groupId = $this->createGroup($this->db, ['instance_id' => 555]);
        $this->assertEquals(555, GroupHelper::instanceId($this->db, $groupId));
        $this->assertEquals(null, GroupHelper::instanceId($this->db, $groupId + 666));
    }

    public function testCreate()
    {
        $groupId = $this->repository->create(
            $type = GroupTypes::CONTENT_SHARING,
            $instanceId = 555,
            $title = 'Testing group',
            $visibility = GroupStatus::PUBLIC,
            $userId = 333,
            $data = ['foo' => 'bar']
        );
        $group = GroupHelper::load($this->db, $groupId);

        $this->assertEquals($type, $group->type);
        $this->assertEquals($instanceId, $group->instance_id);
        $this->assertEquals($title, $group->title);
        $this->assertEquals($visibility, $group->visibility);
        $this->assertEquals($userId, $group->user_id);
        $this->assertEquals((object) $data, $group->data);
    }

    public function testCreateItem()
    {
        $groupId = $this->repository->create(GroupTypes::CONTENT_SHARING, 555, 'Testing group');
        $groupItemId = $this->repository->createItem($groupId, 'lo', 456, GroupItemStatus::ACTIVE);
        $groupItem = GroupHelper::loadItem($this->db, $groupItemId);

        $this->assertEquals($groupId, $groupItem->group_id);
        $this->assertEquals('lo', $groupItem->entity_type);
        $this->assertEquals(456, $groupItem->entity_id);
        $this->assertEquals(GroupItemStatus::ACTIVE, $groupItem->status);
    }

    public function testRemoveItem()
    {
        $groupId = $this->repository->create(GroupTypes::CONTENT_SHARING, 555, 'Testing group');
        $groupItemId = $this->repository->createItem($groupId, 'lo', 456, GroupItemStatus::ACTIVE);

        # Create item
        $groupItem = GroupHelper::loadItem($this->db, $groupItemId);
        $this->assertNotEmpty($groupItem);

        # Remove item.
        $this->repository->removeItem($groupItemId);
        $groupItem = GroupHelper::loadItem($this->db, $groupItemId);
        $this->assertEmpty($groupItem);
    }

    public function testIsItemOf()
    {
        $groupId = $this->createGroup($this->db);
        $this->createGroupItem($this->db, ['group_id' => $groupId, 'entity_type' => 'lo', 'entity_id' => 100]);
        $this->assertNotEmpty(GroupHelper::isItemOf($this->db, 'lo', 100, $groupId));
        $this->assertFalse(GroupHelper::isItemOf($this->db, 'lo', 1001, $groupId));
    }

    public function testCanAccess()
    {
        $c = $this->getContainer();
        $portalId = $this->createPortal($this->db, ['title' => $portalName = 'foo.com']);
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
        $portalId = $this->createPortal($this->db, ['title' => $portalName = 'foo.com']);
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
        $req->attributes->replace(['jwt.payload' => $this->getJwt(null, null, null, Roles::ACCOUNTS_ROLES)], null, null, false);
        $this->assertTrue(GroupHelper::groupAccess($groupUserId, $userId, $accessChecker, $req));

        $instance = 'site.mygo1.com';
        $req->attributes->replace(['jwt.payload' => $this->getJwt(null, null, $instance, [Roles::ADMIN]), null, null, false]);
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
        $instanceId = $this->createPortal($this->db, []);
        $entityId = GroupHelper::getEntityId(GroupItemTypes::PORTAL, $instanceId, null, $this->db);

        $this->assertEquals($instanceId, $entityId);
    }

    public function testGetEntityIdUser()
    {
        $userId = $this->createUser($this->db, ['mail' => 'user@go1.com', 'instance' => 'accounts']);
        $accountId = $this->createUser($this->db, ['mail' => 'user@go1.com']);
        EdgeHelper::link($this->db, $this->queue, EdgeTypes::HAS_ACCOUNT, $userId, $accountId);

        $entityId = GroupHelper::getEntityId(GroupItemTypes::USER, $userId, 'az.mygo1.com', $this->db);

        $this->assertEquals($accountId, $entityId);
    }

    public function testGetEntityIdLo()
    {
        $loId = $this->createCourse($this->db, []);
        $entityId = GroupHelper::getEntityId(GroupItemTypes::LO, $loId, null, $this->db);

        $this->assertEquals($loId, $entityId);
    }

    public function testGetEntityIdNote()
    {
        $noteId = $this->createNote($this->db, []);
        $entityId = GroupHelper::getEntityId(GroupItemTypes::NOTE, 'NOTE_UUID', null, null, $this->db);

        $this->assertEquals($noteId, $entityId);
    }

    public function testGetEntityIdGroup()
    {
        $groupId = $this->createGroup($this->db, []);
        $entityId = GroupHelper::getEntityId(GroupItemTypes::GROUP, $groupId, null, null, null, $this->db);

        $this->assertEquals($groupId, $entityId);
    }

    public function testGetEntityIdAward()
    {
        $awardId = $this->createAward($this->db, []);
        $entityId = GroupHelper::getEntityId(GroupItemTypes::AWARD, $awardId, null, null, null, null, $this->db);

        $this->assertEquals($awardId, $entityId);
    }

    public function testIsContent()
    {
        $groupPremiumId = $this->createGroup($this->db, ['type' => GroupTypes::CONTENT]);
        $groupPremium = GroupHelper::load($this->db, $groupPremiumId);

        $this->assertTrue(GroupHelper::isContent($groupPremium));

        $groupId1 = $this->createGroup($this->db, []);
        $group1 = GroupHelper::load($this->db, $groupId1);

        $this->assertFalse(GroupHelper::isContent($group1));
    }

    public function testIsContentPackage()
    {
        $groupMarketId = $this->createGroup($this->db, ['type' => GroupTypes::CONTENT_PACKAGE]);
        $groupMarket = GroupHelper::load($this->db, $groupMarketId);

        $this->assertTrue(GroupHelper::isContentPackage($groupMarket));

        $groupId1 = $this->createGroup($this->db, []);
        $group1 = GroupHelper::load($this->db, $groupId1);

        $this->assertFalse(GroupHelper::isContentPackage($group1));
    }

    public function testGroupTypePermission()
    {
        $req = new Request;
        $req->attributes->set('jwt.payload', $this->getAdminPayload('az.mygo1.com'));

        $groupMarketId = $this->createGroup($this->db, ['type' => GroupTypes::CONTENT_PACKAGE]);
        $groupMarket = GroupHelper::load($this->db, $groupMarketId);

        $this->assertFalse(GroupHelper::groupTypePermission($groupMarket, $req));

        $groupId = $this->createGroup($this->db, []);
        $group = GroupHelper::load($this->db, $groupId);

        $this->assertTrue(GroupHelper::groupTypePermission($group, $req));
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
        $groupId = $this->createGroup($this->db, ['type' => GroupTypes::CONTENT_PACKAGE, 'data' => ['description' => 'group description']]);
        $group = GroupHelper::load($this->db, $groupId);

        $this->assertTrue(GroupHelper::isContentPackage($group));
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

        $this->assertCount(2, GroupHelper::groupAssignments($this->db, $groupId));
        $this->assertCount(1, GroupHelper::groupAssignments($this->db, $groupId, ['status' => GroupAssignStatuses::ARCHIVED]));
    }

    public function testGetUserGroups()
    {
        $c = $this->getContainer();
        $user1Id = $this->createUser($this->db, ['instance' => $c['accounts_name'], 'mail' => 'user-groups-testing-1@foo.com']);
        $user2Id = $this->createUser($this->db, ['instance' => $c['accounts_name'], 'mail' => 'user-groups-testing-2@foo.com']);
        $user3Id = $this->createUser($this->db, ['instance' => $c['accounts_name'], 'mail' => 'user-groups-testing-3@foo.com']);

        $portalId = $this->createPortal($this->db, ['title' => $portalName = 'foo.com']);

        $account1Id = $this->createUser($this->db, ['instance' => $portalName, 'mail' => 'user-groups-testing-1@foo.com']);
        $account2Id = $this->createUser($this->db, ['instance' => $portalName, 'mail' => 'user-groups-testing-2@foo.com']);
        $account3Id = $this->createUser($this->db, ['instance' => $portalName, 'mail' => 'user-groups-testing-3@foo.com']);

        $group1Id = $this->createGroup($this->db, ['title' => 'Group 1', 'instance_id' => $portalId, 'user_id' => $user1Id]);
        $group2Id = $this->createGroup($this->db, ['title' => 'Group 2', 'instance_id' => $portalId, 'user_id' => $user2Id]);
        $group3Id = $this->createGroup($this->db, ['title' => 'Group 3', 'instance_id' => $portalId, 'user_id' => $user2Id]);
        $group4Id = $this->createGroup($this->db, ['title' => 'Group 4', 'instance_id' => $portalId, 'user_id' => $user3Id]);

        $this->createGroupItem($this->db, ['group_id' => $group1Id, 'entity_id' => $account1Id]);
        $this->createGroupItem($this->db, ['group_id' => $group2Id, 'entity_id' => $account1Id]);
        $this->createGroupItem($this->db, ['group_id' => $group2Id, 'entity_id' => $account2Id]);
        $this->createGroupItem($this->db, ['group_id' => $group3Id, 'entity_id' => $account3Id]);

        $groups = GroupHelper::userGroups($this->db, $this->db, $portalId, $account1Id, $c['accounts_name']);
        $this->assertEquals(['Group 1', 'Group 2'], $groups);

        $groups = GroupHelper::userGroups($this->db, $this->db, $portalId, $account2Id, $c['accounts_name']);
        $this->assertEquals(['Group 2', 'Group 3'], $groups);

        $groups = GroupHelper::userGroups($this->db, $this->db, $portalId, $account3Id, $c['accounts_name']);
        $this->assertEquals(['Group 3', 'Group 4'], $groups);
    }

    public function testHostContentSharingGroup()
    {
        $groupId = $this->createGroup($this->db, ['type' => GroupTypes::CONTENT_SHARING, 'title' => 'go1:lo:345']);

        $this->assertEquals($groupId, GroupHelper::hostContentSharingGroup($this->db, 'lo', 345)->id);
        $this->assertFalse(GroupHelper::hostContentSharingGroup($this->db, 'lo', 456));
    }

    public function testHostIdFromGroupTitle()
    {
        $title = "go1:lo:100:marketplace";
        $this->assertEquals(100, GroupHelper::hostIdFromGroupTitle($title));
        $this->assertEquals('lo', GroupHelper::hostTypeFromGroupTitle($title));
    }

    public function testLoadItemByGroupAndEntity()
    {
        $groupId = $this->repository->create(GroupTypes::CONTENT_SHARING, 555, 'Testing group');
        $groupItemId = $this->repository->createItem($groupId, 'lo', 456, GroupItemStatus::ACTIVE);
        $groupItem = GroupHelper::loadItemByGroupAndEntity($this->db, $groupId, 'lo', 456);

        $this->assertEquals($groupItem->id, $groupItemId);
    }

    public function testFindGroupIdsByItem()
    {
        $groupId = $this->repository->create(GroupTypes::CONTENT_SHARING, 555, 'Testing group');
        $this->repository->createItem($groupId, 'lo', 456, GroupItemStatus::ACTIVE);
        $groupIds = GroupHelper::findGroupIdsByItem($this->db, 'lo', 456);

        $this->assertEquals($groupIds[0], $groupId);
    }

    public function testCountGroupByItem()
    {
        $groupId = $this->repository->create(GroupTypes::CONTENT_SHARING, 555, 'Testing group');
        $this->repository->createItem($groupId, 'lo', 456, GroupItemStatus::ACTIVE);
        $numOfGroup = GroupHelper::countGroupByItem($this->db, 'lo', 456);

        $this->assertEquals(1, $numOfGroup);
    }

    public function testIsPortalSystemGroup()
    {
        $this->assertTrue(GroupHelper::isPortalSystemGroup('go1:portal:1'));
        $this->assertFalse(GroupHelper::isPortalSystemGroup('go1:foo:1'));
        $this->assertFalse(GroupHelper::isPortalSystemGroup('Foo'));
    }

    public function testIsPassiveContentSharing()
    {
        $originalInstanceId = $this->createPortal($this->db, ['title' => $portalName = 'origin.go1.com']);
        $loId = $this->createCourse($this->db, ['title' => 'Sharing Course', 'instance_id' => $originalInstanceId]);

        $groupId = $this->createGroup($this->db, []);
        $sharingGroupId = $this->createGroup($this->db, ['type' => GroupTypes::CONTENT_SHARING, 'title' => "go1:lo:{$loId}"]);
        $marketplaceSharingGroupId = $this->createGroup($this->db, ['type' => GroupTypes::CONTENT_SHARING, 'title' => "go1:lo:{$loId}:marketplace"]);

        $this->assertFalse(GroupHelper::isPassiveContentSharing(GroupHelper::load($this->db, $groupId)));
        $this->assertFalse(GroupHelper::isPassiveContentSharing(GroupHelper::load($this->db, $sharingGroupId)));
        $this->assertTrue(GroupHelper::isPassiveContentSharing(GroupHelper::load($this->db, $marketplaceSharingGroupId)));
    }

    public function dataTestContentSharingTypes()
    {
        return [
            [true],
            [false],
        ];
    }

    /**
     * @dataProvider dataTestContentSharingTypes
     */
    public function testIsMemberOfContentSharingGroup($addToMyPortalContent)
    {
        $originalInstanceId = $this->createPortal($this->db, ['title' => $portalName = 'origin.go1.com']);
        $loId = $this->createCourse($this->db, ['title' => 'Sharing Course', 'instance_id' => $originalInstanceId]);
        $instanceXId = $this->createPortal($this->db, ['title' => $portalName = 'portalX.go1.com']);
        $instanceYId = $this->createPortal($this->db, ['title' => $portalName = 'portalY.go1.com']);
        $instanceZId = $this->createPortal($this->db, ['title' => $portalName = 'portalZ.go1.com']);

        if ($addToMyPortalContent) {
            $sharingGroupId = $this->createGroup($this->db, ['type' => GroupTypes::CONTENT_SHARING, 'title' => "go1:lo:{$loId}:marketplace"]);
        }
        else {
            $sharingGroupId = $this->createGroup($this->db, ['type' => GroupTypes::CONTENT_SHARING, 'title' => "go1:lo:{$loId}"]);
        }
        $this->repository->createItem($sharingGroupId, GroupItemTypes::PORTAL, $instanceXId, GroupItemStatus::ACTIVE);
        $this->repository->createItem($sharingGroupId, GroupItemTypes::PORTAL, $instanceYId, GroupItemStatus::BLOCKED);

        $this->assertTrue(GroupHelper::isMemberOfContentSharingGroup($this->db, $loId, $instanceXId));
        $this->assertTrue(GroupHelper::isMemberOfContentSharingGroup($this->db, $loId, $instanceXId, $addToMyPortalContent));
        $this->assertFalse(GroupHelper::isMemberOfContentSharingGroup($this->db, $loId, $instanceYId));
        $this->assertFalse(GroupHelper::isMemberOfContentSharingGroup($this->db, $loId, $instanceZId));
    }

    public function testIsMemberOfContentSharingGroupWhenBothExisted()
    {
        $originalInstanceId = $this->createPortal($this->db, ['title' => $portalName = 'origin.go1.com']);
        $loId = $this->createCourse($this->db, ['title' => 'Sharing Course', 'instance_id' => $originalInstanceId]);
        $instanceXId = $this->createPortal($this->db, ['title' => $portalName = 'portalX.go1.com']);
        $instanceYId = $this->createPortal($this->db, ['title' => $portalName = 'portalY.go1.com']);
        $instanceZId = $this->createPortal($this->db, ['title' => $portalName = 'portalZ.go1.com']);

        $sharingGroupId = $this->createGroup($this->db, ['type' => GroupTypes::CONTENT_SHARING, 'title' => "go1:lo:{$loId}"]);
        $marketplaceSharingGroupId = $this->createGroup($this->db, ['type' => GroupTypes::CONTENT_SHARING, 'title' => "go1:lo:{$loId}:marketplace"]);

        $this->repository->createItem($sharingGroupId, GroupItemTypes::PORTAL, $instanceXId, GroupItemStatus::ACTIVE);
        $this->repository->createItem($marketplaceSharingGroupId, GroupItemTypes::PORTAL, $instanceXId, GroupItemStatus::BLOCKED);
        $this->assertTrue(GroupHelper::isMemberOfContentSharingGroup($this->db, $loId, $instanceXId));

        $this->repository->createItem($sharingGroupId, GroupItemTypes::PORTAL, $instanceYId, GroupItemStatus::BLOCKED);
        $this->repository->createItem($marketplaceSharingGroupId, GroupItemTypes::PORTAL, $instanceYId, GroupItemStatus::ACTIVE);
        $this->assertTrue(GroupHelper::isMemberOfContentSharingGroup($this->db, $loId, $instanceYId));

        $this->repository->createItem($sharingGroupId, GroupItemTypes::PORTAL, $instanceZId, GroupItemStatus::ACTIVE);
        $this->repository->createItem($marketplaceSharingGroupId, GroupItemTypes::PORTAL, $instanceZId, GroupItemStatus::ACTIVE);
        $this->assertTrue(GroupHelper::isMemberOfContentSharingGroup($this->db, $loId, $instanceZId));
    }

    public function testIsAuthor()
    {
        $c = $this->getContainer();
        $user1Id = $this->createUser($this->db, ['instance' => $c['accounts_name'], 'mail' => 'user-groups-testing-1@foo.com']);
        $group1Id = $this->createGroup($this->db, ['title' => 'Group 1', 'instance_id' => 1, 'user_id' => $user1Id]);
        $group = GroupHelper::load($this->db, $group1Id);
        $this->assertTrue(GroupHelper::isAuthor($group, $user1Id));
    }
}
