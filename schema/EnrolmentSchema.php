<?php

namespace go1\util\schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;

class EnrolmentSchema
{
    public static function install(Schema $schema)
    {
        if (!$schema->hasTable('gc_enrolment')) {
            $enrolment = $schema->createTable('gc_enrolment');
            $enrolment->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
            $enrolment->addColumn('profile_id', 'integer', ['unsigned' => true]);
            $enrolment->addColumn('parent_lo_id', 'integer', ['unsigned' => true, 'notnull' => false, 'default' => 0, 'comment' => '@deprecated: Wrong design, we can not find parent enrolment from this value. This will be soon dropped.']);
            $enrolment->addColumn('parent_enrolment_id', 'integer', ['unsigned' => true, 'notnull' => false, 'default' => 0]);
            $enrolment->addColumn('parent_id', 'integer', ['unsigned' => true, 'notnull' => false, 'default' => 0, 'comment' => 'Parent enrolment ID.']);
            $enrolment->addColumn('lo_id', 'integer', ['unsigned' => true]);
            $enrolment->addColumn('instance_id', 'integer', ['unsigned' => true]);
            $enrolment->addColumn('taken_instance_id', 'integer', ['unsigned' => true]);
            $enrolment->addColumn('start_date', 'datetime', ['notnull' => false]);
            $enrolment->addColumn('end_date', 'datetime', ['notnull' => false]);
            $enrolment->addColumn('status', 'string');
            $enrolment->addColumn('result', 'float', ['notnull' => false]);
            $enrolment->addColumn('pass', 'smallint');
            $enrolment->addColumn('changed', 'datetime', ['unsigned' => true]);
            $enrolment->addColumn('timestamp', 'integer', ['unsigned' => true]);
            $enrolment->addColumn('data', 'blob', ['notnull' => false]);

            $enrolment->setPrimaryKey(['id']);
            $enrolment->addUniqueIndex(['profile_id', 'parent_enrolment_id', 'lo_id', 'taken_instance_id']);
            $enrolment->addIndex(['profile_id']);
            $enrolment->addIndex(['instance_id']);
            $enrolment->addIndex(['parent_lo_id']);
            $enrolment->addIndex(['parent_enrolment_id']);
            $enrolment->addIndex(['taken_instance_id']);
            $enrolment->addIndex(['status']);
            $enrolment->addIndex(['timestamp']);
            $enrolment->addIndex(['changed']);
            $enrolment->addIndex(['lo_id']);
        }

        if (!$schema->hasTable('gc_enrolment_revision')) {
            $revision = $schema->createTable('gc_enrolment_revision');
            $revision->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
            $revision->addColumn('enrolment_id', 'integer', ['unsigned' => true]);
            $revision->addColumn('profile_id', 'integer', ['unsigned' => true]);
            $revision->addColumn('parent_lo_id', 'integer', ['unsigned' => true, 'notnull' => false]);
            $revision->addColumn('parent_id', 'integer', ['unsigned' => true, 'notnull' => false, 'default' => 0, 'comment' => 'Parent enrolment ID.']);
            $revision->addColumn('lo_id', 'integer', ['unsigned' => true]);
            $revision->addColumn('instance_id', 'integer', ['unsigned' => true]);
            $revision->addColumn('taken_instance_id', 'integer', ['unsigned' => true]);
            $revision->addColumn('start_date', 'datetime', ['notnull' => false]);
            $revision->addColumn('end_date', 'datetime', ['notnull' => false]);
            $revision->addColumn('status', 'string');
            $revision->addColumn('result', 'float', ['notnull' => false]);
            $revision->addColumn('pass', 'smallint');
            $revision->addColumn('data', 'blob', ['notnull' => false]);
            $revision->addColumn('note', 'text');
            $revision->addColumn('parent_enrolment_id', 'integer', ['unsigned' => true, 'notnull' => false]);
            $revision->addColumn('timestamp', 'integer', ['unsigned' => true, 'notnull' => false]);

            $revision->setPrimaryKey(['id']);
            $revision->addIndex(['enrolment_id']);
            $revision->addIndex(['profile_id']);
            $revision->addIndex(['parent_lo_id']);
            $revision->addIndex(['parent_id']);
            $revision->addIndex(['lo_id']);
            $revision->addIndex(['instance_id']);
            $revision->addIndex(['taken_instance_id']);
            $revision->addIndex(['status']);
            $revision->addIndex(['pass']);
            $revision->addIndex(['parent_enrolment_id']);
            $revision->addIndex(['timestamp']);
        }

        if (!$schema->hasTable('gc_enrolment_transaction')) {
            $map = $schema->createTable('gc_enrolment_transaction');
            $map->addColumn('enrolment_id', Type::INTEGER, ['unsigned' => true]);
            $map->addColumn('transaction_id', Type::INTEGER, ['unsigned' => true]);
            $map->addColumn('payment_method', Type::STRING);
            $map->addUniqueIndex(['enrolment_id', 'transaction_id']);
            $map->addIndex(['enrolment_id']);
            $map->addIndex(['transaction_id']);
            $map->addIndex(['payment_method']);
        }

        if (!$schema->hasTable('enrolment_stream')) {
            $stream = $schema->createTable('enrolment_stream');
            $stream->addColumn('id', Type::INTEGER, ['unsigned' => true, 'autoincrement' => true]);
            $stream->addColumn('portal_id', Type::INTEGER, ['unsigned' => true]);
            $stream->addColumn('created', Type::INTEGER, ['unsigned' => true]);
            $stream->addColumn('enrolment_id', Type::INTEGER, ['unsigned' => true]);
            $stream->addColumn('actor_id', Type::INTEGER, ['unsigned' => true, 'default' => 0]);
            $stream->addColumn('action', Type::STRING);
            $stream->addColumn('payload', Type::BLOB);
            $stream->setPrimaryKey(['id']);
            $stream->addIndex(['enrolment_id']);
            $stream->addIndex(['portal_id']);
            $stream->addIndex(['actor_id']);
            $stream->addIndex(['created']);
        }

        static::update01($schema);
        static::update02($schema);
    }

    public static function installManualRecord(Schema $schema)
    {
        if (!$schema->hasTable('enrolment_manual')) {
            $manual = $schema->createTable('enrolment_manual');
            $manual->addColumn('id', Type::INTEGER, ['unsigned' => true, 'autoincrement' => true]);
            $manual->addColumn('instance_id', Type::INTEGER, ['unsigned' => true]);
            $manual->addColumn('entity_type', Type::STRING);
            $manual->addColumn('entity_id', Type::STRING);
            $manual->addColumn('user_id', Type::INTEGER, ['unsigned' => true]);
            $manual->addColumn('verified', Type::BOOLEAN);
            $manual->addColumn('data', Type::BLOB, ['notnull' => false]);
            $manual->addColumn('created', Type::INTEGER);
            $manual->addColumn('updated', Type::INTEGER);

            $manual->setPrimaryKey(['id']);
            $manual->addIndex(['user_id']);
            $manual->addIndex(['entity_type']);
            $manual->addIndex(['entity_id']);
            $manual->addUniqueIndex(['user_id', 'instance_id', 'entity_type', 'entity_id']);
            $manual->addIndex(['verified']);
            $manual->addIndex(['created']);
            $manual->addIndex(['updated']);
        }
    }

    public static function update01(Schema $schema)
    {
        if ($schema->hasTable('gc_enrolment')) {
            $enrolment = $schema->getTable('gc_enrolment');
            $indexes = $enrolment->getIndexes();
            foreach ($indexes as $index) {
                if (
                    $index->isUnique()
                    && !$index->isPrimary()
                    && (['profile_id', 'parent_lo_id', 'lo_id'] == $index->getColumns())
                ) {
                    $enrolment->dropIndex($index->getName());
                    $enrolment->addUniqueIndex(['profile_id', 'parent_lo_id', 'lo_id', 'taken_instance_id']);
                }
            }
        }
    }

    public static function update02(Schema $schema)
    {
        if ($schema->hasTable('enrolment_stream')) {
            $stream = $schema->getTable('enrolment_stream');
            if (!$stream->hasColumn('actor_id')) {
                $stream->addColumn('actor_id', Type::INTEGER, ['unsigned' => true, 'default' => 0]);
                $stream->addIndex(['actor_id']);
            }
        }
    }
}
