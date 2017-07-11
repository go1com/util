<?php

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;

class ActionSchema
{
    public static function install(Schema $schema)
    {
        if (!$schema->hasTable('activity')) {
            $activity = $schema->createTable('activity');
            $activity->addColumn('id', Type::INTEGER, ['unsigned' => true, 'autoincrement' => true]);
            $activity->addColumn('instance_id', Type::INTEGER, ['unsigned' => true]);
            $activity->addColumn('actor_id', Type::INTEGER, ['unsigned' => true]);
            $activity->addColumn('entity_type', Type::STRING);
            $activity->addColumn('entity_id', Type::STRING, ['notnull' => false]);
            $activity->addColumn('created', Type::INTEGER, ['unsigned' => true]);
            $activity->addColumn('updated', Type::INTEGER, ['unsigned' => true, 'notnull' => true]);
            $activity->addColumn('data', Type::BLOB, ['notnull' => false]);
            $activity->addIndex(['instance_id']);
            $activity->addIndex(['actor_id']);
            $activity->addIndex(['entity_type']);
            $activity->addIndex(['entity_id']);
            $activity->addIndex(['created']);
            $activity->addIndex(['updated']);
        }
    }
}
