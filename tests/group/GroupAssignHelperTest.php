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
    private $dueDate;
    private $data = ['foo' => 'bar'];

    public function testMerge()
    {
        GroupAssignHelper::merge(
            $this->go1,
            $this->queue,
            $this->groupId,
            $this->instanceId,
            $this->userId,
            $this->entityType,
            $this->entityId
        );

        $this->assertCount(1, $this->queueMessages[Queue::GROUP_ASSIGN_CREATE]);
        $msg = (array)$this->queueMessages[Queue::GROUP_ASSIGN_CREATE][0];
        $this->assertEquals(1, $msg['id']);
        $this->assertEquals($this->groupId, $msg['group_id']);
        $this->assertEquals($this->instanceId, $msg['instance_id']);
        $this->assertEquals($this->entityType, $msg['entity_type']);
        $this->assertEquals($this->entityId, $msg['entity_id']);
        $this->assertEquals($this->userId, $msg['user_id']);
        $this->assertEquals(GroupAssignStatuses::PUBLISHED, $msg['status']);
        $this->assertNull($msg['due_date']);
        $this->assertNull($msg['data']);

        GroupAssignHelper::merge(
            $this->go1,
            $this->queue,
            $this->groupId,
            $this->instanceId,
            $this->userId,
            GroupAssignTypes::LO,
            $this->entityId,
            $this->dueDate = time(),
            $this->data
        );

        $this->assertCount(1, $this->queueMessages[Queue::GROUP_ASSIGN_UPDATE]);
        $msg = (array)$this->queueMessages[Queue::GROUP_ASSIGN_UPDATE][0];
        $this->assertEquals(1, $msg['id']);
        $this->assertEquals($this->groupId, $msg['group_id']);
        $this->assertEquals($this->instanceId, $msg['instance_id']);
        $this->assertEquals($this->entityType, $msg['entity_type']);
        $this->assertEquals($this->entityId, $msg['entity_id']);
        $this->assertEquals($this->userId, $msg['user_id']);
        $this->assertEquals(GroupAssignStatuses::PUBLISHED, $msg['status']);
        $this->assertEquals($this->dueDate, $msg['due_date']);
        $this->assertEquals(json_encode($this->data), $msg['data']);
    }

    public function testLoadBy()
    {
        $this->testMerge();
        $groupAssign = (array)GroupAssignHelper::loadBy($this->go1, $this->groupId, $this->instanceId, $this->entityType, $this->entityId);
        $this->assertEquals(1, $groupAssign['id']);
        $this->assertEquals($this->groupId, $groupAssign['group_id']);
        $this->assertEquals($this->instanceId, $groupAssign['instance_id']);
        $this->assertEquals($this->entityType, $groupAssign['entity_type']);
        $this->assertEquals($this->entityId, $groupAssign['entity_id']);
        $this->assertEquals($this->userId, $groupAssign['user_id']);
        $this->assertEquals(GroupAssignStatuses::PUBLISHED, $groupAssign['status']);
        $this->assertEquals($this->dueDate, $groupAssign['due_date']);
        $this->assertEquals($this->data, $groupAssign['data']);
    }

    public function testArchive()
    {
        $this->testMerge();
        GroupAssignHelper::archive($this->go1, $this->queue, $this->groupId, $this->instanceId, $this->userId, $this->entityType, $this->entityId);

        $this->assertCount(1, $this->queueMessages[Queue::GROUP_ASSIGN_DELETE]);
        $msg = (array)$this->queueMessages[Queue::GROUP_ASSIGN_DELETE][0];
        $this->assertEquals(1, $msg['id']);
        $this->assertEquals($this->groupId, $msg['group_id']);
        $this->assertEquals($this->instanceId, $msg['instance_id']);
        $this->assertEquals($this->entityType, $msg['entity_type']);
        $this->assertEquals($this->entityId, $msg['entity_id']);
        $this->assertEquals($this->userId, $msg['user_id']);
        $this->assertEquals(GroupAssignStatuses::PUBLISHED, $msg['status']);
        $this->assertEquals($this->dueDate, $msg['due_date']);
        $this->assertEquals($this->data, $msg['data']);
    }

}
