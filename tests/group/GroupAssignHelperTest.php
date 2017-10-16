<?php

namespace go1\util\tests\group;

use go1\util\group\GroupAssignHelper;
use go1\util\group\GroupAssignStatuses;
use go1\util\group\GroupAssignTypes;
use go1\util\queue\Queue;
use go1\util\tests\UtilTestCase;

class GroupAssignHelperTest extends UtilTestCase
{
    private $groupId = 1;
    private $instanceId = 1;
    private $userId = 1;
    private $entityType = GroupAssignTypes::LO;
    private $entityId = 1;

    public function testMerge()
    {
        GroupAssignHelper::merge(
            $this->db,
            $this->queue,
            $this->groupId,
            $this->instanceId,
            $this->userId,
            $this->entityType,
            $this->entityId
        );

        $this->assertCount(1, $this->queueMessages[Queue::GROUP_ASSIGN_CREATE]);
        $this->assertArraySubset([
            'id'          => 1,
            'group_id'    => $this->groupId,
            'instance_id' => $this->instanceId,
            'entity_type' => $this->entityType,
            'entity_id'   => $this->entityId,
            'user_id'     => $this->userId,
            'status'      => GroupAssignStatuses::PUBLISHED,
        ], (array) $this->queueMessages[Queue::GROUP_ASSIGN_CREATE][0]);

        GroupAssignHelper::merge(
            $this->db,
            $this->queue,
            $this->groupId,
            $this->instanceId,
            $this->userId,
            GroupAssignTypes::LO,
            $this->entityId
        );

        $this->assertCount(1, $this->queueMessages[Queue::GROUP_ASSIGN_UPDATE]);
        $this->assertArraySubset([
            'id'          => 1,
            'group_id'    => $this->groupId,
            'instance_id' => $this->instanceId,
            'entity_type' => $this->entityType,
            'entity_id'   => $this->entityId,
            'user_id'     => $this->userId,
            'status'      => GroupAssignStatuses::PUBLISHED,
        ], (array) $this->queueMessages[Queue::GROUP_ASSIGN_UPDATE][0]);
    }

    public function testLoadBy()
    {
        $this->testMerge();
        $groupAssign = GroupAssignHelper::loadBy($this->db, $this->groupId, $this->instanceId, $this->entityType, $this->entityId);
        $this->assertArraySubset([
            'id'          => 1,
            'group_id'    => $this->groupId,
            'instance_id' => $this->instanceId,
            'entity_type' => $this->entityType,
            'entity_id'   => $this->entityId,
            'user_id'     => $this->userId,
            'status'      => GroupAssignStatuses::PUBLISHED,
        ], (array) $groupAssign);
    }

    public function testArchive()
    {
        $this->testMerge();
        GroupAssignHelper::archive($this->db, $this->queue, $this->groupId, $this->instanceId, $this->userId, $this->entityType, $this->entityId);

        $this->assertCount(1, $this->queueMessages[Queue::GROUP_ASSIGN_DELETE]);
        $this->assertArraySubset([
            'id'          => 1,
            'group_id'    => $this->groupId,
            'instance_id' => $this->instanceId,
            'entity_type' => $this->entityType,
            'entity_id'   => $this->entityId,
            'user_id'     => $this->userId,
            'status'      => GroupAssignStatuses::PUBLISHED,
        ], (array) $this->queueMessages[Queue::GROUP_ASSIGN_DELETE][0]);
    }

}
