<?php

namespace go1\util\schema;

use Doctrine\DBAL\Schema\Schema;

class VoteSchema
{
    public static function install(Schema $schema)
    {
        if (!$schema->hasTable('vote_items')) {
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

        if (!$schema->hasTable('vote_caches')) {
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
    }
}
