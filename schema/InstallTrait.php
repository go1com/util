<?php

namespace go1\util\schema;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\TableExistsException;
use Doctrine\DBAL\Schema\Comparator;
use Doctrine\DBAL\Schema\Schema;
use go1\kv\KV;
use go1\util\plan\PlanRepository;

trait InstallTrait
{
    public function installGo1Schema(Connection $db, $coreOnly = true)
    {
        $schema = $db->getSchemaManager()->createSchema();
        $compare = new Comparator;
        $origin = clone $schema;

        !$schema->hasTable('gc_kv') && $this->createKeyValueTable($schema);
        !$schema->hasTable('gc_ro') && $this->createRoTable($schema);
        !$schema->hasTable('gc_plan') && PlanRepository::install($schema);
        PortalSchema::install($schema);
        UserSchema::install($schema);
        LoSchema::install($schema);
        EnrolmentSchema::install($schema);

        if (!$coreOnly) {
            SocialSchema::install($schema);
            NoteSchema::install($schema);

            !$schema->hasTable('vote_items') && $this->createVoteItemsTable($schema);
            !$schema->hasTable('vote_caches') && $this->createVoteCachesTable($schema);
            !$schema->hasTable('portal_conf') && $this->createPortalConfTables($schema);
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

    private function createKeyValueTable(Schema $schema)
    {
        if (class_exists(KV::class)) {
            KV::migrate($schema, 'gc_kv');
        }
    }

    private function createRoTable(Schema $schema)
    {
        $edge = $schema->createTable('gc_ro');
        $edge->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
        $edge->addColumn('type', 'integer', ['unsigned' => true]);
        $edge->addColumn('source_id', 'integer', ['unsigned' => true]);
        $edge->addColumn('target_id', 'integer', ['unsigned' => true]);
        $edge->addColumn('weight', 'integer', ['unsigned' => true]);
        $edge->addColumn('data', 'text', ['notnull' => false]);
        $edge->setPrimaryKey(['id']);
        $edge->addIndex(['source_id']);
        $edge->addIndex(['target_id']);
        $edge->addUniqueIndex(['type', 'source_id', 'target_id']);
    }

    private function createVoteItemsTable(Schema $schema)
    {
        $item = $schema->createTable('vote_items');
        $item->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
        $item->addColumn('type', 'integer', ['unsigned' => true]);
        $item->addColumn('entity_type', 'string');
        $item->addColumn('entity_id', 'string');
        $item->addColumn('profile_id', 'integer', ['unsigned' => true]);
        $item->addColumn('value', 'integer');
        $item->addColumn('timestamp', 'integer');
        $item->setPrimaryKey(['id']);
        $item->addIndex(['type']);
        $item->addIndex(['entity_type']);
        $item->addIndex(['entity_id']);
        $item->addIndex(['profile_id']);
        $item->addIndex(['value']);
        $item->addIndex(['timestamp']);
    }

    private function createVoteCachesTable(Schema $schema)
    {
        $cache = $schema->createTable('vote_caches');
        $cache->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
        $cache->addColumn('type', 'string');
        $cache->addColumn('entity_type', 'string');
        $cache->addColumn('entity_id', 'string');
        $cache->addColumn('percent', 'float');
        $cache->addColumn('data', 'text');
        $cache->setPrimaryKey(['id']);
        $cache->addIndex(['type'], 'idx_vote_cache_type');
        $cache->addIndex(['entity_type'], 'idx_vote_cache_entity_type');
        $cache->addIndex(['entity_id'], 'idx_vote_cache_entity_id');
        $cache->addUniqueIndex(['type', 'entity_type', 'entity_id'], 'unq_vote_caches');
    }

    private function createPortalConfTables(Schema $schema)
    {
        $conf = $schema->createTable('portal_conf');
        $conf->addColumn('instance', 'string');
        $conf->addColumn('namespace', 'string');
        $conf->addColumn('name', 'string');
        $conf->addColumn('public', 'smallint');
        $conf->addColumn('data', 'blob');
        $conf->addColumn('timestamp', 'integer');
        $conf->setPrimaryKey(['instance', 'namespace', 'name']);
        $conf->addIndex(['instance', 'namespace']);
        $conf->addIndex(['public']);
        $conf->addIndex(['timestamp']);
    }
}
