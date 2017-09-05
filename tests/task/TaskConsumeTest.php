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

    private function taskClass(bool $comlete = false, bool $error = false)
    {
        $db = $this->db;
        $client = $this->queue;
        $event = $this->taskEvent;
        $taskName = $this->taskName;
        $taskItemName = $this->taskItemName;

        return new class($db, $client, $event, $taskName, $taskItemName, $comlete, $error) extends TaskConsumer {
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

            protected function processTask(string $type = 'task_type')
            {
                parent::processTask($type);
            }

            protected function processTaskItem(int $taskId, string $type = 'task_item_type')
            {
                parent::processTaskItem($taskId, $type);

                if ($this->complete) {
                    $taskItem = $this->getTaskItem($taskId);
                    $taskItem->setStatus(TaskItem::STATUS_COMPLETED);
                    TaskHelper::updateTaskItemStatus($this->db, $taskItem);

                    $this->completeTask();
                }

                if ($this->error) {
                    $taskItem = $this->getTaskItem($taskId);
                    $taskItem->setStatus(TaskItem::STATUS_FAILED);
                    TaskHelper::updateTaskItemStatus($this->db, $taskItem);

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
        $this->assertEquals(Task::STATUS_PROCESSING, $task->getStatus());
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
        $this->assertEquals(TaskItem::STATUS_PROCESSING, $taskItem->getStatus());
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
        $this->assertEquals(TaskItem::STATUS_COMPLETED, $taskItem1->getStatus());

        $consumer = $this->taskClass(true);
        $consumer->consume('', (object)['task' => $this->taskItemName, 'task_id' => $taskId]);
        $taskItem2 = TaskHelper::loadTaskItem($this->db, $taskItemId2, $this->taskItemName);
        $this->assertEquals(TaskItem::STATUS_COMPLETED, $taskItem2->getStatus());

        $task = TaskHelper::loadTask($this->db, $taskId, $this->taskName);
        $this->assertEquals(Task::STATUS_COMPLETED, $task->getStatus());
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
//        $this->assertEquals(TaskItem::STATUS_FAILED, $taskItem1->getStatus());
        $data = $taskItem1->getData();
        $this->assertEquals("Failed to process task item.", $data['error']);


    }
}
