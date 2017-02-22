<?php

namespace go1\util\schema;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\TableExistsException;
use Doctrine\DBAL\Schema\Comparator;
use Doctrine\DBAL\Schema\Schema;
use go1\flood\Flood;
use go1\kv\KV;

trait InstallTrait
{
    public function installGo1Schema(Connection $db, $coreOnly = true)
    {
        $schema = $db->getSchemaManager()->createSchema();
        $compare = new Comparator;
        $origin = clone $schema;
        !$schema->hasTable('gc_domain') && $this->createDomainTable($schema);
        !$schema->hasTable('gc_enrolment') && $this->createEnrolmentTable($schema);
        !$schema->hasTable('gc_flood') && $this->createFloodTable($schema);
        !$schema->hasTable('gc_instance') && $this->createInstanceTable($schema);
        !$schema->hasTable('gc_kv') && $this->createKeyValueTable($schema);
        !$schema->hasTable('gc_lo') && $this->createLoTable($schema);
        !$schema->hasTable('gc_lo_pricing') && $this->createLoPricingTable($schema);
        !$schema->hasTable('gc_event') && $this->createEventTable($schema);
        !$schema->hasTable('gc_tag') && $this->createLoTag($schema);
        !$schema->hasTable('gc_outcome') && $this->createOutcomeTable($schema);
        !$schema->hasTable('gc_ro') && $this->createRoTable($schema);
        !$schema->hasTable('gc_role') && $this->createRoleTable($schema);
        !$schema->hasTable('gc_user') && $this->createUserTable($schema);
        !$schema->hasTable('gc_user_locale') && $this->createUserLocales($schema);
        !$schema->hasTable('gc_user_mail') && $this->createUserMailTable($schema);
        !$schema->hasTable('gc_user_filter') && $this->createUserFilterTable($schema);

        if (!$coreOnly) {
            $this->createUserStreamTable($schema);
            $this->createUserStreamCommentTable($schema);
            $this->createUserStreamFlagTable($schema);
        }

        $diff = $compare->compare($origin, $schema);
        foreach ($diff->toSql($db->getDatabasePlatform()) as $sql) {
            try {
                $db->executeQuery($sql);
            }
            catch (TableExistsException $e) {
            }
        }
    }

    private function createDomainTable(Schema $schema)
    {
        $table = $schema->createTable('gc_domain');
        $table->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
        $table->addColumn('title', 'string');
        $table->setPrimaryKey(['id']);
        $table->addIndex(['title'], 'index_title');
    }

    private function createEnrolmentTable(Schema $schema)
    {
        $enrolment = $schema->createTable('gc_enrolment');
        $enrolment->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
        $enrolment->addColumn('profile_id', 'integer', ['unsigned' => true]);
        $enrolment->addColumn('parent_lo_id', 'integer', ['unsigned' => true, 'notnull' => false, 'default' => 0]);
        $enrolment->addColumn('lo_id', 'integer', ['unsigned' => true]);
        $enrolment->addColumn('instance_id', 'integer', ['unsigned' => true]);
        $enrolment->addColumn('taken_instance_id', 'integer', ['unsigned' => true]);
        $enrolment->addColumn('start_date', 'datetime');
        $enrolment->addColumn('end_date', 'datetime', ['notnull' => false]);
        $enrolment->addColumn('status', 'string');
        $enrolment->addColumn('result', 'float', ['notnull' => false]);
        $enrolment->addColumn('pass', 'smallint');
        $enrolment->addColumn('changed', 'integer', ['unsigned' => true]);
        $enrolment->addColumn('timestamp', 'integer', ['unsigned' => true]);
        $enrolment->addColumn('data', 'blob', ['notnull' => false]);
        $enrolment->setPrimaryKey(['id']);
        $enrolment->addUniqueIndex(['profile_id', 'parent_lo_id', 'lo_id']);
        $enrolment->addIndex(['profile_id']);
        $enrolment->addIndex(['instance_id']);
        $enrolment->addIndex(['taken_instance_id']);
        $enrolment->addIndex(['status']);
        $enrolment->addIndex(['timestamp']);
        $enrolment->addIndex(['changed']);
        $enrolment->addIndex(['lo_id']);

        $revision = $schema->createTable('gc_enrolment_revision');
        $revision->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
        $revision->addColumn('enrolment_id', 'integer', ['unsigned' => true]);
        $revision->addColumn('profile_id', 'integer', ['unsigned' => true]);
        $revision->addColumn('parent_lo_id', 'integer', ['unsigned' => true, 'notnull' => false]);
        $revision->addColumn('lo_id', 'integer', ['unsigned' => true]);
        $revision->addColumn('instance_id', 'integer', ['unsigned' => true]);
        $revision->addColumn('taken_instance_id', 'integer', ['unsigned' => true]);
        $revision->addColumn('start_date', 'datetime');
        $revision->addColumn('end_date', 'datetime', ['notnull' => false]);
        $revision->addColumn('status', 'string');
        $revision->addColumn('result', 'float', ['notnull' => false]);
        $revision->addColumn('pass', 'smallint');
        $revision->addColumn('note', 'text');
        $revision->setPrimaryKey(['id']);
        $revision->addIndex(['profile_id']);
        $revision->addIndex(['instance_id']);
        $revision->addIndex(['taken_instance_id']);
        $revision->addIndex(['status']);
        $revision->addIndex(['lo_id']);
    }

    private function createFloodTable(Schema $schema)
    {
        if (class_exists(Flood::class)) {
            Flood::migrate($schema, 'gc_flood');
        }
    }

    private function createInstanceTable(Schema $schema)
    {
        $table = $schema->createTable('gc_instance');
        $table->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
        $table->addColumn('title', 'string');
        $table->addColumn('status', 'smallint');
        $table->addColumn('is_primary', 'smallint');
        $table->addColumn('version', 'string');
        $table->addColumn('data', 'blob');
        $table->addColumn('timestamp', 'integer', ['unsigned' => true]);
        $table->addColumn('created', 'integer', ['unsigned' => true]);

        $table->setPrimaryKey(['id']);
        $table->addIndex(['title']);
        $table->addIndex(['status']);
        $table->addIndex(['is_primary']);
        $table->addIndex(['timestamp']);
        $table->addIndex(['created']);
    }

    private function createKeyValueTable(Schema $schema)
    {
        if (class_exists(KV::class)) {
            KV::migrate($schema, 'gc_kv');
        }
    }

    private function createLoTable(Schema $schema)
    {
        $table = $schema->createTable('gc_lo');
        $table->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
        $table->addColumn('type', 'string');
        $table->addColumn('language', 'string');
        $table->addColumn('instance_id', 'integer', ['unsigned' => true]);
        $table->addColumn('remote_id', 'integer');
        $table->addColumn('origin_id', 'integer');
        $table->addColumn('title', 'string');
        $table->addColumn('description', 'text');
        $table->addColumn('private', 'smallint');
        $table->addColumn('published', 'smallint');
        $table->addColumn('marketplace', 'smallint');
        $table->addColumn('image', 'string', ['notnull' => false]);
        $table->addColumn('event', 'text', ['notnull' => false]);
        $table->addColumn('event_start', 'integer', ['unsigned' => true, 'notnull' => false]);
        $table->addColumn('locale', 'string', ['notnull' => false]);
        $table->addColumn('tags', 'string');
        $table->addColumn('timestamp', 'integer', ['unsigned' => true]);
        $table->addColumn('data', 'blob');
        $table->addColumn('created', 'integer');
        $table->addColumn('updated', 'integer', ['notnull' => false]);
        $table->addColumn('sharing', 'smallint');

        $table->setPrimaryKey(['id']);
        $table->addIndex(['type']);
        $table->addIndex(['instance_id']);
        $table->addIndex(['title']);
        $table->addIndex(['language']);
        $table->addIndex(['private']);
        $table->addIndex(['published']);
        $table->addIndex(['marketplace']);
        $table->addIndex(['event_start']);
        $table->addIndex(['timestamp']);
        $table->addIndex(['created']);
        $table->addIndex(['updated']);
        $table->addIndex(['sharing']);
        $table->addIndex(['tags']);
        $table->addIndex(['locale']);
        $table->addUniqueIndex(['instance_id', 'type', 'remote_id']);
    }

    private function createLoPricingTable(Schema $schema)
    {
        $table = $schema->createTable('gc_lo_pricing');
        $table->addColumn('id', 'integer', ['unsigned' => true]);
        $table->addColumn('price', 'float');
        $table->addColumn('currency', 'string', ['length' => 4]);
        $table->addColumn('tax', 'float');
        $table->addColumn('tax_included', 'smallint', ['default' => 1]);

        $table->setPrimaryKey(['id']);
        $table->addIndex(['price']);
    }

    private function createEventTable(Schema $schema)
    {
        $table = $schema->createTable('gc_event');
        $table->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
        $table->addColumn('start', 'string');
        $table->addColumn('end', 'string', ['notnull' => false]);
        $table->addColumn('timezone', 'string', ['length' => 3]);
        $table->addColumn('seats', 'integer', ['notnull' => false]);
        $table->addColumn('loc_country', 'string');
        $table->addColumn('loc_administrative_area', 'string', ['notnull' => false]);
        $table->addColumn('loc_sub_administrative_area', 'string', ['notnull' => false]);
        $table->addColumn('loc_locality', 'string', ['notnull' => false]);
        $table->addColumn('loc_dependent_locality', 'string', ['notnull' => false]);
        $table->addColumn('loc_thoroughfare', 'string', ['notnull' => false]);
        $table->addColumn('loc_premise', 'string', ['notnull' => false]);
        $table->addColumn('loc_sub_premise', 'string', ['notnull' => false]);
        $table->addColumn('loc_organisation_name', 'string', ['notnull' => false]);
        $table->addColumn('loc_name_line', 'string', ['notnull' => false]);
        $table->addColumn('loc_postal_code', 'integer', ['notnull' => false]);
        $table->addColumn('created', 'integer');
        $table->addColumn('updated', 'integer');
        $table->addColumn('data', 'blob');

        $table->setPrimaryKey(['id']);
        $table->addIndex(['start']);
        $table->addIndex(['end']);
        $table->addIndex(['loc_country']);
        $table->addIndex(['loc_administrative_area']);
        $table->addIndex(['loc_sub_administrative_area']);
        $table->addIndex(['loc_locality']);
        $table->addIndex(['loc_dependent_locality']);
        $table->addIndex(['loc_thoroughfare']);
        $table->addIndex(['loc_premise']);
        $table->addIndex(['loc_sub_premise']);
        $table->addIndex(['loc_organisation_name']);
        $table->addIndex(['loc_name_line']);
        $table->addIndex(['loc_postal_code']);
        $table->addIndex(['created']);
        $table->addIndex(['updated']);
    }

    /**
     * @TODO Remove children & lo_count columns.
     * @param Schema $schema
     */
    private function createLoTag(Schema $schema)
    {
        $table = $schema->createTable('gc_tag');
        $table->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
        $table->addColumn('title', 'string');
        $table->addColumn('instance_id', 'integer', ['unsigned' => true]);
        $table->addColumn('parent_id', 'integer', ['unsigned' => true]);
        $table->addColumn('children', 'text', ['description' => 'Children IDs, separated by comma.', 'notnull' => false]);
        $table->addColumn('lo_count', 'integer', ['default' => 0, 'description' => '@TODO: We do not really need this.']);
        $table->addColumn('weight', 'integer', ['default' => 0]);
        $table->addColumn('timestamp', 'integer', ['unsigned' => true]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['instance_id', 'title']);
        $table->addIndex(['instance_id']);
        $table->addIndex(['parent_id']);
        $table->addIndex(['weight']);
        $table->addIndex(['timestamp']);
        $table->addForeignKeyConstraint('gc_instance', ['instance_id'], ['id']);
    }

    private function createOutcomeTable(Schema $schema)
    {
        $table = $schema->createTable('gc_outcome');
        $table->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
        $table->addColumn('lo_id', 'integer', ['unsigned' => true]);
        $table->addColumn('profile_id', 'integer', ['unsigned' => true]);
        $table->addColumn('outcome', 'integer', ['unsigned' => true]);
        $table->addColumn('completion_rate', 'integer', ['unsigned' => true, 'size' => 'tiny', 'default' => 0]);
        $table->addColumn('remote_id', 'integer', ['unsigned' => true, 'notnull' => false]);

        $table->setPrimaryKey(['id']);
        $table->addIndex(['lo_id']);
        $table->addIndex(['profile_id']);
        $table->addIndex(['remote_id']);
        $table->addUniqueIndex(['lo_id', 'profile_id']);
        $table->addForeignKeyConstraint('gc_lo', ['lo_id'], ['id']);
    }

    private function createRoTable(Schema $schema)
    {
        $table = $schema->createTable('gc_ro');
        $table->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
        $table->addColumn('type', 'integer', ['unsigned' => true]);
        $table->addColumn('source_id', 'integer', ['unsigned' => true]);
        $table->addColumn('target_id', 'integer', ['unsigned' => true]);
        $table->addColumn('weight', 'integer', ['unsigned' => true]);
        $table->addColumn('data', 'text', ['notnull' => false]);

        $table->setPrimaryKey(['id']);
        $table->addIndex(['source_id']);
        $table->addIndex(['target_id']);
        $table->addUniqueIndex(['type', 'source_id', 'target_id']);
    }

    private function createUserTable(Schema $schema)
    {
        $table = $schema->createTable('gc_user');
        $table->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
        $table->addColumn('uuid', 'string');
        $table->addColumn('name', 'string', ['notnull' => false]);
        $table->addColumn('instance', 'string');
        $table->addColumn('profile_id', 'integer', ['unsigned' => true]);
        $table->addColumn('mail', 'string');
        $table->addColumn('password', 'string');
        $table->addColumn('created', 'integer');
        $table->addColumn('access', 'integer');
        $table->addColumn('login', 'integer');
        $table->addColumn('status', 'integer');
        $table->addColumn('first_name', 'string');
        $table->addColumn('last_name', 'string');
        $table->addColumn('allow_public', 'integer', ['default' => 0]);
        $table->addColumn('data', 'text');
        $table->addColumn('timestamp', 'integer');

        $table->setPrimaryKey(['id']);
        $table->addIndex(['name']);
        $table->addIndex(['uuid']);
        $table->addIndex(['mail']);
        $table->addIndex(['created']);
        $table->addIndex(['login']);
        $table->addIndex(['timestamp']);
        $table->addIndex(['instance']);
        $table->addUniqueIndex(['uuid']);
        $table->addUniqueIndex(['instance', 'mail']);
    }

    private function createRoleTable(Schema $schema)
    {
        $table = $schema->createTable('gc_role');
        $table->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
        $table->addColumn('instance', 'string');
        $table->addColumn('rid', 'integer', ['unsigned' => true]);
        $table->addColumn('name', 'string');
        $table->addColumn('weight', 'integer', ['size' => 'tiny', 'default' => 0]);
        $table->addColumn('permission', 'text', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['instance', 'name', 'weight']);
    }

    private function createUserLocales(Schema $schema)
    {
        $table = $schema->createTable('gc_user_locale');
        $table->addColumn('id', 'integer', ['unsigned' => true]);
        $table->addColumn('locale', 'string', ['length' => 12]);
        $table->addColumn('weight', 'integer', ['unsigned' => true, 'default' => 0]);
        $table->setPrimaryKey(['id', 'locale']);
        $table->addIndex(['locale']);
        $table->addIndex(['weight']);
        $table->addForeignKeyConstraint('gc_user', ['id'], ['id']);
    }

    private function createUserStreamTable(Schema $schema)
    {
        $connection = $schema->createTable('gc_stream');
        $connection->addColumn('li_id', 'integer');
        $connection->addColumn('profile_id', 'integer');
        $connection->addColumn('created', 'integer');
        $connection->addColumn('relevant', 'integer', ['length' => 2]);
        $connection->addIndex(['li_id']);
        $connection->addIndex(['profile_id']);
    }

    private function createUserStreamCommentTable(Schema $schema)
    {
        $connection = $schema->createTable('gc_stream_comment');
        $connection->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
        $connection->addColumn('pid', 'integer');
        $connection->addColumn('li_id', 'integer');
        $connection->addColumn('profile_id', 'integer');
        $connection->addColumn('created', 'integer');
        $connection->addColumn('comment', 'string', ['length' => 500]);
        $connection->addIndex(['li_id']);
    }

    private function createUserStreamFlagTable(Schema $schema)
    {
        $connection = $schema->createTable('gc_stream_flag');
        $connection->addColumn('li_id', 'integer');
        $connection->addColumn('profile_id', 'integer');
        $connection->addColumn('created', 'integer');
        $connection->addIndex(['li_id']);
        $connection->addIndex(['profile_id']);
    }

    private function createUserMailTable(Schema $schema)
    {
        $table = $schema->createTable('gc_user_mail');
        $table->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
        $table->addColumn('title', 'string');
        $table->setPrimaryKey(['id']);
        $table->addIndex(['title']);
    }

    private function createUserFilterTable(Schema $schema)
    {
        $table = $schema->createTable('gc_user_filter');
        $table->addColumn('id', 'integer');
        $table->addColumn('type', 'string');
        $table->addColumn('identifier', 'string');
        $table->addColumn('created', 'integer');
        $table->setPrimaryKey(['id']);
        $table->addIndex(['type', 'identifier']);
    }
}
