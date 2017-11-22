<?php

namespace go1\util\tests\task;

use Doctrine\DBAL\Connection;
use go1\clients\MqClient;
use go1\util\schema\mock\TaskMockTrait;
use go1\util\task\Task;
use go1\util\task\TaskConsumer;
use go1\util\task\TaskHelper;
use go1\util\task\TaskItem;
use go1\util\tests\UtilTestCase;

class TaskConsumeTest extends UtilTestCase
{
    use TaskMockTrait;

    protected $taskService = 'service';

    private $taskName = 'service_task';
    private $taskItemName = 'service_task_item';
    private $taskEvent = 'service_event';

    private $taskType = 'task_type';
    private $taskItemType = 'task_item_type';

    private function taskClass(bool $complete = false, bool $error = false)
    {
        $db = $this->db;
        $client = $this->queue;
        $event = $this->taskEvent;
        $taskName = $this->taskName;
        $taskItemName = $this->taskItemName;

        return new class($db, $client, $event, $taskName, $taskItemName, $complete, $error) extends TaskConsumer {
            private $complete;
            private $error;

            public function __construct(
                Connection $db,
                MqClient $mqClient,
                string $event,
                string $taskName,
                string $taskItemName,
                bool $complete,
                bool $error
            )
            {
                parent::__construct($db, $mqClient, $event, $taskName, $taskItemName);

                $this->complete = $complete;
                $this->error = $error;
            }

            protected function processTask(int $taskId = 0, string $type = 'task_type')
            {
                parent::processTask($taskId, $type);
            }

            protected function processTaskItem(int $taskId, string $type = 'task_item_type')
            {
                parent::processTaskItem($taskId, $type);

                if ($this->complete) {
                    $taskItem = $this->getTaskItem($taskId);
                    $taskItem->status = Task::STATUS_COMPLETED;
                    TaskHelper::updateTaskStatus($this->db, $taskItem->id, $taskItem->status, $taskItem->name);

                    $this->completeTask();
                }

                if ($this->error) {
                    $taskItem = $this->getTaskItem($taskId);
                    $taskItem->status = Task::STATUS_FAILED;
                    TaskHelper::updateTaskStatus($this->db, $taskItem->id, $taskItem->status, $taskItem->name);

                    $this->error('Failed to process task item.');
                }
            }
        };
    }


    public function testProcessTask()
    {
        $taskId = $this->createTask($this->db, [
            'name' => $this->taskName,
            'data' => ['type' => $this->taskType, 'lo_id' => 1000]
        ]);

        $consumer = $this->taskClass();
        $consumer->consume('', (object)['task' => $this->taskName]);

        $task = TaskHelper::loadTask($this->db, $taskId, $this->taskName);
        $this->assertEquals(Task::STATUS_PROCESSING, $task->status);
    }

    public function testProcessTaskItem()
    {
        $taskId = $this->createTask($this->db, [
            'name' => $this->taskName,
            'data' => ['type' => $this->taskType, 'lo_id' => 1000]
        ]);

        $taskItemId1 = $this->createTaskItem($this->db, [
            'name'    => $this->taskItemName,
            'task_id' => $taskId,
            'data'    => ['type' => $this->taskItemType, 'lo_id' => 1000]
        ]);

        $this->createTaskItem($this->db, [
            'name'    => $this->taskItemName,
            'task_id' => $taskId,
            'data'    => ['type' => $this->taskItemType, 'lo_id' => 1001]
        ]);

        $consumer = $this->taskClass();
        $consumer->consume('', (object)['task' => $this->taskName]);
        $consumer->consume('', (object)['task' => $this->taskItemName, 'task_id' => $taskId]);

        $taskItem = TaskHelper::loadTaskItem($this->db, $taskItemId1, $this->taskItemName);
        $this->assertEquals(TaskItem::STATUS_PROCESSING, $taskItem->status);
    }

    public function testProcessTaskItemComplete()
    {
        $taskId = $this->createTask($this->db, [
            'name' => $this->taskName,
            'data' => ['type' => $this->taskType, 'lo_id' => 1000]
        ]);

        $taskItemId1 = $this->createTaskItem($this->db, [
            'name'    => $this->taskItemName,
            'task_id' => $taskId,
            'data'    => ['type' => $this->taskItemType, 'lo_id' => 1000]
        ]);

        $taskItemId2 = $this->createTaskItem($this->db, [
            'name'    => $this->taskItemName,
            'task_id' => $taskId,
            'data'    => ['type' => $this->taskItemType, 'lo_id' => 1001]
        ]);

        $consumer = $this->taskClass(true);
        $consumer->consume('', (object)['task' => $this->taskName]);

        $consumer = $this->taskClass(true);
        $consumer->consume('', (object)['task' => $this->taskItemName, 'task_id' => $taskId]);
        $taskItem1 = TaskHelper::loadTaskItem($this->db, $taskItemId1, $this->taskItemName);
        $this->assertEquals(TaskItem::STATUS_COMPLETED, $taskItem1->status);

        $task = TaskHelper::loadTask($this->db, $taskId, $this->taskName);
        $this->assertEquals(Task::STATUS_PROCESSING, $task->status);

        $consumer = $this->taskClass(true);
        $consumer->consume('', (object)['task' => $this->taskItemName, 'task_id' => $taskId]);
        $taskItem2 = TaskHelper::loadTaskItem($this->db, $taskItemId2, $this->taskItemName);
        $this->assertEquals(TaskItem::STATUS_COMPLETED, $taskItem2->status);

        $task = TaskHelper::loadTask($this->db, $taskId, $this->taskName);
        $this->assertEquals(Task::STATUS_COMPLETED, $task->status);
    }

    public function testProcessTaskItemError()
    {
        $taskId = $this->createTask($this->db, [
            'name' => $this->taskName,
            'data' => ['type' => $this->taskType, 'lo_id' => 1000]
        ]);

        $taskItemId1 = $this->createTaskItem($this->db, [
            'name'    => $this->taskItemName,
            'task_id' => $taskId,
            'data'    => ['type' => $this->taskItemType, 'lo_id' => 1000]
        ]);

        $consumer = $this->taskClass();
        $consumer->consume('', (object)['task' => $this->taskName]);

        $consumer = $this->taskClass(false, true);
        $consumer->consume('', (object)['task' => $this->taskItemName, 'task_id' => $taskId]);
        $taskItem1 = TaskHelper::loadTaskItem($this->db, $taskItemId1, $this->taskItemName);
        $this->assertEquals(TaskItem::STATUS_FAILED, $taskItem1->status);
        $this->assertEquals("Failed to process task item.", $taskItem1->data['error']);
    }

    public function testProcessTaskParallel()
    {
        $taskId1 = $this->createTask($this->db, [
            'name' => $this->taskName,
            'data' => ['type' => $this->taskType, 'lo_id' => 1001]
        ]);

        $taskId2 = $this->createTask($this->db, [
            'name' => $this->taskName,
            'data' => ['type' => $this->taskType, 'lo_id' => 1002]
        ]);

        $taskId3 = $this->createTask($this->db, [
            'name' => $this->taskName,
            'data' => ['type' => $this->taskType, 'lo_id' => 1003]
        ]);

        $consumer = $this->taskClass();
        $consumer->consume('', (object)['task' => $this->taskName, 'task_id' => $taskId1]);
        $consumer->consume('', (object)['task' => $this->taskName, 'task_id' => $taskId2]);
        $consumer->consume('', (object)['task' => $this->taskName, 'task_id' => $taskId3]);

        $task1 = TaskHelper::loadTask($this->db, $taskId1, $this->taskName);
        $this->assertEquals(Task::STATUS_PROCESSING, $task1->status);

        $task2 = TaskHelper::loadTask($this->db, $taskId2, $this->taskName);
        $this->assertEquals(Task::STATUS_PROCESSING, $task2->status);

        $task3 = TaskHelper::loadTask($this->db, $taskId3, $this->taskName);
        $this->assertEquals(Task::STATUS_PROCESSING, $task3->status);
    }
}
