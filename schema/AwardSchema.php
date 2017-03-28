<?php

namespace go1\util\schema;

use Doctrine\DBAL\Schema\Schema;

class AwardSchema
{
    public static function install(Schema $schema)
    {
        if (!$schema->hasTable('award_award')) {
            $award = $schema->createTable('award_award');
            $award->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
            $award->addColumn('revision_id', 'integer', ['notnull' => false]);
            $award->addColumn('instance_id', 'integer');
            $award->addColumn('user_id', 'integer');
            $award->addColumn('title', 'string');
            $award->addColumn('description', 'text', ['notnull' => false]);
            $award->addColumn('tags', 'string');
            $award->addColumn('locale', 'string', ['notnull' => false]);
            $award->addColumn('data', 'blob');
            $award->addColumn('published', 'boolean');
            $award->addColumn('quantity', 'integer', ['notnull' => false, 'description' => 'Target quantity']);
            $award->addColumn('expire', 'string', ['notnull' => false, 'description' => 'Award expire time']);
            $award->addColumn('created', 'integer');
            $award->setPrimaryKey(['id']);
            $award->addIndex(['revision_id']);
            $award->addIndex(['instance_id']);
            $award->addIndex(['user_id']);
            $award->addIndex(['title']);
            $award->addIndex(['tags']);
            $award->addIndex(['locale']);
            $award->addIndex(['published']);
            $award->addIndex(['quantity']);
            $award->addIndex(['expire']);
            $award->addIndex(['created']);
        }

        if (!$schema->hasTable('award_revision')) {
            $revision = $schema->createTable('award_revision');
            $revision->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
            $revision->addColumn('award_id', 'integer', ['unsigned' => true]);
            $revision->addColumn('updated', 'integer');
            $revision->setPrimaryKey(['id']);
            $revision->addIndex(['award_id']);
            $revision->addIndex(['updated']);
        }

        if (!$schema->hasTable('award_item')) {
            $item = $schema->createTable('award_item');
            $item->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
            $item->addColumn('award_revision_id', 'integer', ['unsigned' => true]);
            $item->addColumn('entity_id', 'integer', ['description' => 'Learning object ID.']);
            $item->addColumn('quantity', 'integer', ['notnull' => false, 'description' => 'Number of item to be awarded.']);
            $item->setPrimaryKey(['id']);
            $item->addIndex(['award_revision_id']);
            $item->addIndex(['entity_id']);
        }

        if (!$schema->hasTable('award_achievement')) {
            $achievement = $schema->createTable('award_achievement');
            $achievement->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
            $achievement->addColumn('user_id', 'integer', ['unsigned' => true]);
            $achievement->addColumn('award_item_id', 'integer', ['unsigned' => true]);
            $achievement->addColumn('created', 'integer');
            $achievement->setPrimaryKey(['id']);
            $achievement->addIndex(['user_id']);
            $achievement->addIndex(['award_item_id']);
            $achievement->addIndex(['created']);
        }
    }
}
