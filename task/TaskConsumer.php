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
        if ($taskName) {
            switch ($taskName) {
                case $this->taskName:
                    $this->processTask();
                    break;

                case $this->taskItemName:
                    $this->processTaskItem($message->task_id);
                    break;

            }
        }

        return true;
    }

    protected function getTask()
    {
        if (!$this->task) {
            $this->task = TaskHelper::loadTaskByStatus($this->db, Task::STATUS_PENDING, $this->taskName);
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

    protected function processTask(string $type = ''){
        $task = $this->getTask();
        if ($task && ($task->getDataType() == $type)) {
            $task->setStatus(Task::STATUS_PROCESSING);
            TaskHelper::updateTaskStatus($this->db, $task);
            $this->task = $task;
        }
    }

    protected function processTaskItem(int $taskId, string $type = '')
    {
        $taskItem = $this->getTaskItem($taskId);
        if ($taskItem && ($taskItem->getDataType() == $type)) {
            $taskItem->setStatus(Task::STATUS_PROCESSING);
            TaskHelper::updateTaskItemStatus($this->db, $taskItem);
            $this->taskItem = $taskItem;
        }
    }

    protected function error(string $message)
    {
        $data = $this->taskItem->getData();
        $data['error'] = $message;
        TaskHelper::updateTaskData($this->db, $this->taskItemName, $this->taskItem->getId(), $data);
    }

    /**
     * The task is completed if there is not pending task items
     */
    protected function completeTask()
    {
        $task = TaskHelper::loadTask($this->db, $this->taskItem->getTaskId(), $this->taskName);
        $name = $this->taskItemName;
        $sql = "SELECT 1 FROM {$name} WHERE task_id = ? AND status = ?";
        $isCompleted = $this->db->fetchColumn($sql, [$task->getId(), TaskItem::STATUS_PENDING]);
        if ($isCompleted) {
            $task->setStatus(Task::STATUS_COMPLETED);
            TaskHelper::updateTaskStatus($this->db, $task);
        }
    }
}
