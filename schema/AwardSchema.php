<?php

namespace go1\util\schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;
use go1\util\award\AwardStatuses;

class AwardSchema
{
    public static function install(Schema $schema)
    {
        if (!$schema->hasTable('award_award')) {
            $award = $schema->createTable('award_award');
            $award->addColumn('id', Type::INTEGER, ['unsigned' => true, 'autoincrement' => true]);
            $award->addColumn('revision_id', Type::INTEGER, ['notnull' => false]);
            $award->addColumn('instance_id', Type::INTEGER);
            $award->addColumn('user_id', Type::INTEGER);
            $award->addColumn('title', Type::STRING);
            $award->addColumn('description', Type::TEXT, ['notnull' => false]);
            $award->addColumn('tags', Type::STRING);
            $award->addColumn('locale', Type::STRING, ['notnull' => false]);
            $award->addColumn('data', Type::BLOB);
            $award->addColumn('published', Type::BOOLEAN);
            $award->addColumn('quantity', Type::FLOAT, ['notnull' => false, 'description' => 'Target quantity']);
            $award->addColumn('expire', Type::STRING, ['notnull' => false, 'description' => 'Award expire time']);
            $award->addColumn('created', Type::INTEGER);
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
            $revision->addColumn('id', Type::INTEGER, ['unsigned' => true, 'autoincrement' => true]);
            $revision->addColumn('award_id', Type::INTEGER, ['unsigned' => true]);
            $revision->addColumn('updated', Type::INTEGER);
            $revision->setPrimaryKey(['id']);
            $revision->addIndex(['award_id']);
            $revision->addIndex(['updated']);
        }

        if (!$schema->hasTable('award_item')) {
            $item = $schema->createTable('award_item');
            $item->addColumn('id', Type::INTEGER, ['unsigned' => true, 'autoincrement' => true]);
            $item->addColumn('award_revision_id', Type::INTEGER, ['unsigned' => true]);
            $item->addColumn('entity_id', Type::INTEGER, ['description' => 'Learning object ID.']);
            $item->addColumn('quantity', Type::FLOAT, ['notnull' => false, 'description' => 'Number of item quantity.']);
            $item->addColumn('weight', Type::INTEGER, ['unsigned' => true]);
            $item->setPrimaryKey(['id']);
            $item->addIndex(['award_revision_id']);
            $item->addIndex(['entity_id']);
            $item->addIndex(['weight']);
        }

        if (!$schema->hasTable('award_item_manual')) {
            $itemManual = $schema->createTable('award_item_manual');
            $itemManual->addColumn('id', Type::INTEGER, ['unsigned' => true, 'autoincrement' => true]);
            $itemManual->addColumn('award_id', Type::INTEGER, ['unsigned' => true]);
            $itemManual->addColumn('title', Type::STRING, ['notnull' => false]);
            $itemManual->addColumn('type', Type::STRING, ['notnull' => false]);
            $itemManual->addColumn('description', Type::STRING, ['notnull' => false]);
            $itemManual->addColumn('user_id', Type::INTEGER, ['unsigned' => true]);
            $itemManual->addColumn('entity_id', Type::INTEGER, ['description' => 'Learning object ID.', 'notnull' => false]);
            $itemManual->addColumn('verified', Type::BOOLEAN);
            $itemManual->addColumn('verifier_id', Type::INTEGER, ['unsigned' => true, 'notnull' => false]);
            $itemManual->addColumn('quantity', Type::FLOAT, ['notnull' => false, 'description' => 'Number of item quantity.']);
            $itemManual->addColumn('completion_date', Type::INTEGER, ['unsigned' => true]);
            $itemManual->addColumn('data', Type::BLOB);
            $itemManual->addColumn('published', Type::BOOLEAN, ['default' => AwardStatuses::PUBLISHED]);
            $itemManual->addColumn('weight', Type::INTEGER, ['unsigned' => true]);
            $itemManual->setPrimaryKey(['id']);
            $itemManual->addIndex(['award_id']);
            $itemManual->addIndex(['user_id']);
            $itemManual->addIndex(['entity_id']);
            $itemManual->addIndex(['verified']);
            $itemManual->addIndex(['verifier_id']);
            $itemManual->addIndex(['published']);
            $itemManual->addIndex(['weight']);
        }

        if (!$schema->hasTable('award_achievement')) {
            $achievement = $schema->createTable('award_achievement');
            $achievement->addColumn('id', Type::INTEGER, ['unsigned' => true, 'autoincrement' => true]);
            $achievement->addColumn('user_id', Type::INTEGER, ['unsigned' => true]);
            $achievement->addColumn('award_item_id', Type::INTEGER, ['unsigned' => true]);
            $achievement->addColumn('created', Type::INTEGER);
            $achievement->setPrimaryKey(['id']);
            $achievement->addIndex(['user_id']);
            $achievement->addIndex(['award_item_id']);
            $achievement->addIndex(['created']);
        }
    }
}
