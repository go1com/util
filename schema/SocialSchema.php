<?php

namespace go1\util\schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;

class SocialSchema
{
    public static function install(Schema $schema)
    {
        if (!$schema->hasTable('social_group')) {
            $group = $schema->createTable('social_group');
            $group->addColumn('id', Type::INTEGER, ['unsigned' => true, 'autoincrement' => true]);
            $group->addColumn('title', Type::STRING);
            $group->addColumn('user_id', Type::INTEGER, ['unsigned' => true, 'comment' => 'Author of group']);
            $group->addColumn('instance_id', Type::INTEGER, ['unsigned' => true]);
            $group->addColumn('visibility', Type::INTEGER);
            $group->addColumn('created', Type::INTEGER, ['unsigned' => true]);
            $group->addColumn('updated', Type::INTEGER, ['unsigned' => true]);
            $group->addColumn('data', Type::BLOB);
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
            $item->addColumn('id', Type::INTEGER, ['unsigned' => true, 'autoincrement' => true]);
            $item->addColumn('group_id', Type::INTEGER);
            $item->addColumn('entity_type', Type::STRING);
            $item->addColumn('entity_id', Type::INTEGER);
            $item->addColumn('status', Type::INTEGER);
            $item->addColumn('created', Type::INTEGER, ['unsigned' => true]);
            $item->addColumn('updated', Type::INTEGER, ['unsigned' => true]);
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

        if (!$schema->hasTable('gc_social_tag')) {
            $tag = $schema->createTable('gc_social_tag');
            $tag->addColumn('id', Type::INTEGER, ['unsigned' => true, 'autoincrement' => true]);
            $tag->addColumn('user_id', Type::INTEGER, ['unsigned' => true]);
            $tag->addColumn('tag_id', Type::INTEGER, ['unsigned' => true]);
            $tag->setPrimaryKey(['id']);
            $tag->addIndex(['user_id']);
            $tag->addIndex(['tag_id']);
        }

        if (!$schema->hasTable('social_group_assign')) {
            $assign = $schema->createTable('social_group_assign');
            $assign->addColumn('group_id', Type::INTEGER, ['unsigned' => true]);
            $assign->addColumn('instance_id', Type::INTEGER, ['unsigned' => true]);
            $assign->addColumn('entity_type', Type::STRING);
            $assign->addColumn('entity_id', Type::INTEGER, ['unsigned' => true]);
            $assign->addColumn('status', Type::INTEGER);
            $assign->addColumn('timestamp', Type::INTEGER, ['unsigned' => true]);
            $assign->addIndex(['instance_id']);
            $assign->addIndex(['entity_type']);
            $assign->addIndex(['entity_id']);
            $assign->addUniqueIndex(['group_id', 'instance_id', 'entity_type', 'entity_id']);
        }
    }
}
