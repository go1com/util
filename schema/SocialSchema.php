<?php

namespace go1\util\schema;

use Doctrine\DBAL\Schema\Schema;

class SocialSchema
{
    public static function install(Schema $schema)
    {
        if (!$schema->hasTable('social_group')) {
            $group = $schema->createTable('social_group');
            $group->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
            $group->addColumn('title', 'string');
            $group->addColumn('user_id', 'integer', ['unsigned' => true, 'comment' => 'Author of group']);
            $group->addColumn('instance_id', 'integer', ['unsigned' => true]);
            $group->addColumn('visibility', 'integer');
            $group->addColumn('created', 'integer', ['unsigned' => true]);
            $group->addColumn('updated', 'integer', ['unsigned' => true]);
            $group->setPrimaryKey(['id']);
            $group->addIndex(['title']);
            $group->addIndex(['user_id']);
            $group->addIndex(['instance_id']);
            $group->addIndex(['visibility']);
            $group->addIndex(['created']);
            $group->addIndex(['updated']);
        }

        if (!$schema->hasTable('social_group_item')) {
            $item = $schema->createTable('social_group_item');
            $item->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
            $item->addColumn('group_id', 'integer');
            $item->addColumn('entity_type', 'string');
            $item->addColumn('entity_id', 'integer');
            $item->addColumn('status', 'integer');
            $item->addColumn('created', 'integer', ['unsigned' => true]);
            $item->addColumn('updated', 'integer', ['unsigned' => true]);
            $item->setPrimaryKey(['id']);
            $item->addIndex(['group_id']);
            $item->addIndex(['entity_type']);
            $item->addIndex(['entity_id']);
            $item->addIndex(['status']);
            $item->addIndex(['created']);
            $item->addIndex(['updated']);
            $item->addUniqueIndex(['group_id', 'entity_type', 'entity_id']);
            $item->addForeignKeyConstraint('social_group', ['group_id'], ['id']);
        }
    }
}
