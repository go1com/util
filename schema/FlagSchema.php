<?php

namespace go1\util\schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;

class FlagSchema
{
    public static function install(Schema $schema)
    {
        if (!$schema->hasTable('flag_item')) {
            $item = $schema->createTable('flag_item');
            $item->addColumn('id', Type::INTEGER, ['unsigned' => true, 'autoincrement' => true]);
            $item->addColumn('entity_type', Type::STRING);
            $item->addColumn('entity_id', Type::INTEGER, ['unsigned' => true]);
            $item->addColumn('level', Type::SMALLINT, ['unsigned' => true]);
            $item->setPrimaryKey(['id']);
            $item->addIndex(['entity_type', 'entity_id']);
        }

        if (!$schema->hasTable('flag')) {
            $flag = $schema->createTable('flag');
            $flag->addColumn('id', Type::INTEGER, ['unsigned' => true, 'autoincrement' => true]);
            $flag->addColumn('user_id', Type::INTEGER, ['unsigned' => true]);
            $flag->addColumn('flag_id', Type::INTEGER, ['unsigned' => true]);
            $flag->addColumn('reason', Type::SMALLINT, ['unsigned' => true]);
            $flag->addColumn('level', Type::SMALLINT, ['unsigned' => true]);
            $flag->addColumn('description', Type::STRING);
            $flag->addColumn('created', Type::INTEGER);
            $flag->addColumn('updated', Type::INTEGER);
            $flag->setPrimaryKey(['id']);
            $flag->addIndex(['created']);
            $flag->addIndex(['updated']);
            $flag->addForeignKeyConstraint('flag_item', ['flag_id'], ['id'], [], 'fk_flag_flag_item');
        }
    }
}