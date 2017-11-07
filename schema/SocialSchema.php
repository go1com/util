<?php

namespace go1\util\schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;
use go1\util\group\GroupTypes;

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
            $group->addColumn('type', Type::STRING, ['default' => GroupTypes::DEFAULT]);
            $group->addColumn('visibility', Type::INTEGER);
            $group->addColumn('created', Type::INTEGER, ['unsigned' => true]);
            $group->addColumn('updated', Type::INTEGER, ['unsigned' => true]);
            $group->addColumn('data', Type::BLOB);
            $group->setPrimaryKey(['id']);
            $group->addIndex(['title']);
            $group->addIndex(['user_id']);
            $group->addIndex(['instance_id']);
            $group->addIndex(['type']);
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
            $assign->addColumn('id', Type::INTEGER, ['unsigned' => true, 'autoincrement' => true]);
            $assign->addColumn('group_id', Type::INTEGER, ['unsigned' => true]);
            $assign->addColumn('instance_id', Type::INTEGER, ['unsigned' => true]);
            $assign->addColumn('entity_type', Type::STRING);
            $assign->addColumn('entity_id', Type::INTEGER, ['unsigned' => true]);
            $assign->addColumn('user_id', Type::INTEGER, ['unsigned' => true]);
            $assign->addColumn('status', Type::INTEGER);
            $assign->addColumn('created', Type::INTEGER, ['unsigned' => true]);
            $assign->addColumn('updated', Type::INTEGER, ['unsigned' => true]);
            $assign->addColumn('due_date', Type::STRING, ['notnull' => false, 'description' => 'due date of assignment, can be absolute or relative']);
            $assign->addColumn('data', Type::BLOB, ['notnull' => false]);
            $assign->setPrimaryKey(['id']);
            $assign->addIndex(['instance_id']);
            $assign->addIndex(['entity_type']);
            $assign->addIndex(['entity_id']);
            $assign->addIndex(['user_id']);
            $assign->addIndex(['status']);
            $assign->addIndex(['created']);
            $assign->addIndex(['updated']);
            $assign->addIndex(['due_date']);
        }

        static::update01($schema);
    }

    private static function update01(Schema $schema)
    {
        $socialAssign = $schema->getTable('social_group_assign');
        if (!$socialAssign->hasColumn('due_date')) {
            $socialAssign->addColumn('due_date', Type::STRING, ['notnull' => false, 'description' => 'due date of assignment, can be absolute or relative']);
            $socialAssign->addIndex(['due_date']);
        }
        if (!$socialAssign->hasColumn('data')) {
            $socialAssign->addColumn('data', Type::BLOB, ['notnull' => false]);
        }
    }
}
