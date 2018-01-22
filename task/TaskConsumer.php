<?php

namespace go1\util\task;

use Doctrine\DBAL\Connection;
use go1\clients\MqClient;
use go1\util\contract\ConsumerInterface;
use stdClass;

class TaskConsumer implements ConsumerInterface
{
    protected $db;
    protected $mqClient;
    protected $event;
    protected $taskName;
    protected $taskItemName;

    /** @var  Task $task */
    protected $task;

    /** @var  TaskItem $taskItem */
    protected $taskItem;

    public function __construct(
        Connection $db,
        MqClient $mqClient,
        string $event,
        string $taskName,
        string $taskItemName
    )
    {
        $this->db = $db;
        $this->mqClient = $mqClient;
        $this->event = $event;
        $this->taskName = $taskName;
        $this->taskItemName = $taskItemName;
    }

    public function aware(string $event): bool
    {
        return $this->event == $event;
    }

    public function consume(string $routingKey, stdClass $message, stdClass $context = null): bool
    {
        $taskName = $message->task ?? null;
        $taskId = $message->task_id ?? 0;
        if ($taskName) {
            switch ($taskName) {
                case $this->taskName:
                    $this->processTask($taskId);
                    break;

                case $this->taskItemName:
                    $this->processTaskItem($taskId);
                    break;
            }
        }

        return true;
    }

    protected function getTask(int $taskId = 0)
    {
        if (!$this->task) {
            $this->task = $taskId
                ? TaskHelper::loadTask($this->db, $taskId, $this->taskName)
                : TaskHelper::loadTaskByStatus($this->db, Task::STATUS_PENDING, $this->taskName);
        }
        else {
            if ($taskId && ($this->task->id != $taskId)) {
                $this->task = TaskHelper::loadTask($this->db, $taskId, $this->taskName);
            }
        }

        return $this->task;
    }

    protected function getTaskItem(int $taskId)
    {
        if (!$this->taskItem) {
            $this->taskItem = TaskHelper::loadTaskItemByStatus($this->db, $taskId, Task::STATUS_PENDING, $this->taskItemName);
        }

        return $this->taskItem;
    }

    protected function processTask(int $taskId = 0, string $type = '')
    {
        $task = $this->getTask($taskId);
        if ($task && ($task->getDataType() == $type)) {
            $task->status = Task::STATUS_PROCESSING;
            TaskHelper::updateTaskStatus($this->db, $task->id, $task->status, $task->name);
            $this->task = $task;
        }
    }

    protected function processTaskItem(int $taskId, string $type = '')
    {
        $taskItem = $this->getTaskItem($taskId);
        if ($taskItem && ($taskItem->getDataType() == $type)) {
            $taskItem->status = Task::STATUS_PROCESSING;
            TaskHelper::updateTaskStatus($this->db, $taskItem->id, $taskItem->status, $taskItem->name);
            $this->taskItem = $taskItem;
        }
    }

    protected function error(string $message)
    {
        $data = $this->taskItem->data;
        $data['error'] = $message;
        TaskHelper::updateTaskData($this->db, $this->taskItem->id, $data, $this->taskItem->name);
    }

    /**
     * The task is completed if there is not pending task items
     */
    protected function completeTask()
    {
        $task = TaskHelper::loadTask($this->db, $this->taskItem->taskId, $this->taskName);
        $sql = "SELECT 1 FROM {$this->taskItemName} WHERE task_id = ? AND status = ?";
        $pending = $this->db->fetchColumn($sql, [$task->id, TaskItem::STATUS_PENDING]);
        if (!$pending) {
            TaskHelper::updateTaskStatus($this->db, $task->id, Task::STATUS_COMPLETED, $this->taskName);
        }
        $this->taskItem = null;
    }
}
