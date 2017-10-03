<?php

namespace go1\util\schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;

class ActivitySchema
{
    public static function install(Schema $schema)
    {
        if (!$schema->hasTable('activity')) {
            $activity = $schema->createTable('activity');
            $activity->addColumn('id', Type::INTEGER, ['unsigned' => true, 'autoincrement' => true]);
            $activity->addColumn('instance_id', Type::INTEGER, ['unsigned' => true]);
            $activity->addColumn('actor_id', Type::INTEGER, ['unsigned' => true, 'notnull' => false]);
            $activity->addColumn('user_id', Type::INTEGER, ['unsigned' => true, 'notnull' => false]);
            $activity->addColumn('action_id', Type::INTEGER, ['unsigned' => true]);
            $activity->addColumn('entity_type', Type::STRING);
            $activity->addColumn('entity_id', Type::STRING);
            $activity->addColumn('created', Type::INTEGER, ['unsigned' => true]);
            $activity->addColumn('updated', Type::INTEGER, ['unsigned' => true]);
            $activity->addColumn('data', Type::BLOB, ['notnull' => false]);
            $activity->setPrimaryKey(['id']);
            $activity->addIndex(['instance_id']);
            $activity->addIndex(['actor_id']);
            $activity->addIndex(['user_id']);
            $activity->addIndex(['entity_type']);
            $activity->addIndex(['entity_id']);
            $activity->addIndex(['created']);
            $activity->addIndex(['updated']);
        }
    }
}
