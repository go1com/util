<?php

namespace go1\util\schema\mock;

use Doctrine\DBAL\Connection;
use go1\util\task\Task;
use go1\util\task\TaskItem;
use Ramsey\Uuid\Uuid;

trait TaskMockTrait
{
    protected function createTask(Connection $db, array $options)
    {
        $data = isset($options['data']) ? (is_scalar($options['data']) ? json_decode($options['data'], true) : $options['data']) : [];
        $db->insert($options['name'], [
            'instance_id' => $options['instance_id'] ?? 1,
            'user_id'     => $options['user_id'] ?? 1,
            'created'     => $options['created'] ?? time(),
            'data'        => $encoded = json_encode($data),
            'updated'     => time(),
            'status'      => $options['status'] ?? Task::STATUS_PENDING,
            'checksum'    => $options['checksum'] ?? md5($encoded)
        ]);

        return $db->lastInsertId($options['name']);
    }

    protected function createTaskItem(Connection $db, array $options)
    {
        $data = isset($options['data']) ? (is_scalar($options['data']) ? json_decode($options['data'], true) : $options['data']) : [];
        $db->insert($options['name'], [
            'task_id' => $options['task_id'],
            'created' => time(),
            'data'    => json_encode($data),
            'status'  => $options['status'] ?? TaskItem::STATUS_PENDING,
        ]);

        return $db->lastInsertId($options['name']);
    }
}
