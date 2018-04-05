<?php

namespace go1\util\schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;
use go1\util\award\AwardItemTypes;
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
            $award->addColumn('marketplace', Type::BOOLEAN);
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
            $award->addIndex(['marketplace']);
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
            $item->addColumn('parent_award_item_id', Type::INTEGER, ['unsigned' => true, 'notnull' => false]);
            $item->addColumn('award_revision_id', Type::INTEGER, ['unsigned' => true]);
            $item->addColumn('type', Type::STRING, ['description' => 'Item types: lo, li, award', 'default' => AwardItemTypes::LO]);
            $item->addColumn('entity_id', Type::INTEGER, ['description' => 'Learning object ID.']);
            $item->addColumn('quantity', Type::FLOAT, ['notnull' => false, 'description' => 'Number of item quantity.']);
            $item->addColumn('weight', Type::INTEGER, ['unsigned' => true]);
            $item->addColumn('mandatory', Type::BOOLEAN, ['default' => false]);
            $item->setPrimaryKey(['id']);
            $item->addIndex(['parent_award_item_id']);
            $item->addIndex(['award_revision_id']);
            $item->addIndex(['type']);
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
            $itemManual->addColumn('assigner_id', Type::INTEGER, ['unsigned' => true, 'notnull' => false]);
            $itemManual->addColumn('entity_id', Type::INTEGER, ['notnull' => false, 'description' => 'Learning object ID.']); // deprecated
            $itemManual->addColumn('verified', Type::BOOLEAN);
            $itemManual->addColumn('verifier_id', Type::INTEGER, ['unsigned' => true, 'notnull' => false]);
            $itemManual->addColumn('quantity', Type::FLOAT, ['notnull' => false, 'description' => 'Number of item quantity.']);
            $itemManual->addColumn('completion_date', Type::INTEGER, ['unsigned' => true]);
            $itemManual->addColumn('categories', Type::STRING, ['notnull' => false]);
            $itemManual->addColumn('data', Type::BLOB);
            $itemManual->addColumn('published', Type::BOOLEAN, ['default' => AwardStatuses::PUBLISHED]);
            $itemManual->addColumn('weight', Type::INTEGER, ['unsigned' => true]);
            $itemManual->addColumn('pass', Type::BOOLEAN, ['default' => false]);
            $itemManual->setPrimaryKey(['id']);
            $itemManual->addIndex(['award_id']);
            $itemManual->addIndex(['user_id']);
            $itemManual->addIndex(['assigner_id']);
            $itemManual->addIndex(['entity_id']);
            $itemManual->addIndex(['verified']);
            $itemManual->addIndex(['verifier_id']);
            $itemManual->addIndex(['categories']);
            $itemManual->addIndex(['published']);
            $itemManual->addIndex(['weight']);
            $itemManual->addIndex(['pass']);
        }

        if (!$schema->hasTable('award_achievement')) {
            $achievement = $schema->createTable('award_achievement');
            $achievement->addColumn('id', Type::INTEGER, ['unsigned' => true, 'autoincrement' => true]);
            $achievement->addColumn('user_id', Type::INTEGER, ['unsigned' => true]);
            $achievement->addColumn('award_item_id', Type::INTEGER, ['unsigned' => true]);
            $achievement->addColumn('created', Type::INTEGER);
            $achievement->addColumn('quantity', Type::FLOAT, ['notnull' => false, 'description' => 'Number of award item current quantity']);
            $achievement->addColumn('expire', Type::INTEGER, ['notnull' => false, 'unsigned' => true, 'description' => 'Award item expire time']);
            $achievement->setPrimaryKey(['id']);
            $achievement->addIndex(['user_id']);
            $achievement->addIndex(['award_item_id']);
            $achievement->addIndex(['quantity']);
            $achievement->addIndex(['expire']);
            $achievement->addIndex(['created']);
        }

        if (!$schema->hasTable('award_enrolment')) {
            $enrolment = $schema->createTable('award_enrolment');
            $enrolment->addColumn('id', Type::INTEGER, ['unsigned' => true, 'autoincrement' => true]);
            $enrolment->addColumn('award_id', Type::INTEGER, ['unsigned' => true]);
            $enrolment->addColumn('user_id', Type::INTEGER, ['unsigned' => true]);
            $enrolment->addColumn('instance_id', Type::INTEGER, ['unsigned' => true]);
            $enrolment->addColumn('expire', Type::INTEGER, ['unsigned' => true, 'notnull' => false]);
            $enrolment->addColumn('start_date', Type::INTEGER, ['notnull' => false]);
            $enrolment->addColumn('end_date', Type::INTEGER, ['notnull' => false]);
            $enrolment->addColumn('status', Type::SMALLINT, ['description' => ['1: In progress, 2: Completed, 3: Expired']]);
            $enrolment->addColumn('quantity', Type::FLOAT, ['description' => 'Number of award enrolment current quantity', 'default' => 0.0]);
            $enrolment->addColumn('data', 'blob', ['notnull' => false]);
            $enrolment->addColumn('created', 'integer', ['unsigned' => true]);
            $enrolment->addColumn('updated', 'integer', ['unsigned' => true]);

            $enrolment->setPrimaryKey(['id']);
            $enrolment->addUniqueIndex(['award_id', 'user_id', 'instance_id']);
            $enrolment->addIndex(['award_id']);
            $enrolment->addIndex(['user_id']);
            $enrolment->addIndex(['instance_id']);
            $enrolment->addIndex(['start_date']);
            $enrolment->addIndex(['end_date']);
            $enrolment->addIndex(['status']);
            $enrolment->addIndex(['quantity']);
            $enrolment->addIndex(['created']);
            $enrolment->addIndex(['updated']);
        }

        if (!$schema->hasTable('award_enrolment_revision')) {
            $enrolmentRevision = $schema->createTable('award_enrolment_revision');
            $enrolmentRevision->addColumn('id', Type::INTEGER, ['unsigned' => true, 'autoincrement' => true]);
            $enrolmentRevision->addColumn('award_enrolment_id', Type::INTEGER, ['unsigned' => true]);
            $enrolmentRevision->addColumn('award_id', Type::INTEGER, ['unsigned' => true]);
            $enrolmentRevision->addColumn('user_id', Type::INTEGER, ['unsigned' => true]);
            $enrolmentRevision->addColumn('expire', Type::DATETIME, ['unsigned' => true, 'notnull' => false]);
            $enrolmentRevision->addColumn('start_date', Type::INTEGER, ['notnull' => false]);
            $enrolmentRevision->addColumn('end_date', Type::INTEGER, ['notnull' => false]);
            $enrolmentRevision->addColumn('status', Type::SMALLINT);
            $enrolmentRevision->addColumn('quantity', Type::FLOAT, ['default' => 0.0]);
            $enrolmentRevision->addColumn('data', 'blob', ['notnull' => false]);
            $enrolmentRevision->addColumn('created', 'integer', ['unsigned' => true]);
            $enrolmentRevision->setPrimaryKey(['id']);
            $enrolmentRevision->addIndex(['award_enrolment_id']);
            $enrolmentRevision->addIndex(['award_id']);
            $enrolmentRevision->addIndex(['user_id']);
            $enrolmentRevision->addIndex(['start_date']);
            $enrolmentRevision->addIndex(['end_date']);
            $enrolmentRevision->addIndex(['status']);
            $enrolmentRevision->addIndex(['quantity']);
            $enrolmentRevision->addIndex(['created']);
        }

        if (!$schema->hasTable('award_item_enrolment')) {
            $itemEnrolment = $schema->createTable('award_item_enrolment');
            $itemEnrolment->addColumn('id', Type::INTEGER, ['unsigned' => true, 'autoincrement' => true]);
            $itemEnrolment->addColumn('award_id', Type::INTEGER, ['unsigned' => true]);
            $itemEnrolment->addColumn('user_id', Type::INTEGER, ['unsigned' => true]);
            $itemEnrolment->addColumn('instance_id', Type::INTEGER, ['unsigned' => true]);
            $itemEnrolment->addColumn('entity_id', Type::INTEGER, ['unsigned' => true]);
            $itemEnrolment->addColumn('type', Type::STRING);
            $itemEnrolment->addColumn('status', Type::STRING);
            $itemEnrolment->addColumn('pass', Type::SMALLINT);
            $itemEnrolment->addColumn('quantity', Type::FLOAT, ['default' => 0.0]);
            $itemEnrolment->addColumn('remote_id', Type::INTEGER, ['unsigned' => true]);
            $itemEnrolment->setPrimaryKey(['id']);
            $itemEnrolment->addIndex(['award_id']);
            $itemEnrolment->addIndex(['user_id']);
            $itemEnrolment->addIndex(['instance_id']);
            $itemEnrolment->addIndex(['entity_id']);
        }

        self::update($schema);
    }

    private static function update(Schema $schema)
    {
        $awardItem = $schema->getTable('award_item');
        if ($awardItem->hasColumn('type')) {
            $type = $awardItem->getColumn('type');
            if (AwardItemTypes::LO != $type->getDefault()) {
                $type->setDefault(AwardItemTypes::LO);
            }
        }
    }
}
