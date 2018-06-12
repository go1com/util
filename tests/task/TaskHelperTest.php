<?php

namespace go1\util\tests\task;

use go1\util\schema\mock\TaskMockTrait;
use go1\util\task\Task;
use go1\util\task\TaskHelper;
use go1\util\task\TaskItem;
use go1\util\tests\UtilTestCase;

class TaskHelperTest extends UtilTestCase
{
    use TaskMockTrait;

    protected $taskService = 'service';

    private $taskName = 'service_task';
    private $taskItemName = 'service_task_item';

    public function testLoadTaskByStatus()
    {
        $this->createTask($this->db, [
            'name' => $this->taskName,
            'data' => ['type' => 'task_type', 'lo_id' => 1000]
        ]);

        $task = TaskHelper::loadTaskByStatus($this->db, Task::STATUS_PENDING, $this->taskName);

        $this->assertTrue($task instanceof Task);
        $this->assertEquals('task_type', $task->getDataType());

        $this->assertEquals(1000, $task->data['lo_id']);
    }

    public function testLoadTaskItemByStatus()
    {
        $taskId = $this->createTask($this->db, [
            'name' => $this->taskName,
            'data' => ['type' => 'task_type', 'lo_id' => 1000]
        ]);

        $this->createTaskItem($this->db, [
            'name'    => $this->taskItemName,
            'task_id' => $taskId,
            'data'    => ['type' => 'task_item_type', 'lo_id' => 1000]
        ]);

        $taskItem = TaskHelper::loadTaskItemByStatus($this->db, $taskId, Task::STATUS_PENDING, $this->taskItemName);

        $this->assertTrue($taskItem instanceof TaskItem);
        $this->assertEquals('task_item_type', $taskItem->getDataType());

        $this->assertEquals(1000, $taskItem->data['lo_id']);
    }

    public function testChecksum()
    {
        $taskId = $this->createTask($this->db, [
            'name' => $this->taskName,
            'data' => $data = ['type' => 'task_type', 'lo_id' => 1000]
        ]);

        $this->assertEquals($taskId, TaskHelper::checksum($this->db, $this->taskName, json_encode($data)));
        $this->assertFalse(TaskHelper::checksum($this->db, $this->taskName, 'NEW_TASK'));
    }

    public function testChecksumWithExpireDay()
    {
        $this->createTask($this->db, [
            'name'    => $this->taskName,
            'created' => strtotime('-2 days', time()),
            'data'    => $data = ['type' => 'task_type_other', 'lo_id' => 1000]
        ]);

        $this->assertFalse(TaskHelper::checksum($this->db, $this->taskName, json_encode($data), 1));
    }
}
