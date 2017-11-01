<?php

namespace go1\util\task;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;

class TaskSchema
{
    public static function install(Schema $schema, string $name)
    {
        $taskName = "{$name}_task";
        if (!$schema->hasTable($taskName)) {
            $task = $schema->createTable($taskName);
            $task->addColumn('id', Type::INTEGER, ['unsigned' => true, 'autoincrement' => true]);
            $task->addColumn('user_id', Type::INTEGER, ['unsigned' => true, 'comment' => 'Author of group']);
            $task->addColumn('instance_id', Type::INTEGER, ['unsigned' => true]);
            $task->addColumn('status', Type::SMALLINT, ['unsigned' => true]);
            $task->addColumn('created', Type::INTEGER, ['unsigned' => true]);
            $task->addColumn('updated', Type::INTEGER, ['unsigned' => true]);
            $task->addColumn('data', 'blob');
            $task->addColumn('checksum', Type::STRING, ['length' => 32]);
            $task->setPrimaryKey(['id']);
            $task->addIndex(['user_id']);
            $task->addIndex(['instance_id']);
            $task->addIndex(['status']);
            $task->addIndex(['checksum']);
        }

        $taskItemName = "{$name}_task_item";
        if (!$schema->hasTable($taskItemName)) {
            $task = $schema->createTable($taskItemName);
            $task->addColumn('id', Type::INTEGER, ['unsigned' => true, 'autoincrement' => true]);
            $task->addColumn('task_id', Type::INTEGER, ['unsigned' => true]);
            $task->addColumn('status', Type::SMALLINT, ['unsigned' => true]);
            $task->addColumn('created', Type::INTEGER, ['unsigned' => true]);
            $task->addColumn('data', 'blob');
            $task->setPrimaryKey(['id']);
            $task->addIndex(['task_id']);
            $task->addIndex(['status']);
        }
    }
}
