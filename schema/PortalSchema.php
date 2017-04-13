<?php

namespace go1\util\schema;

use Doctrine\DBAL\Schema\Schema;

class PortalSchema
{
    public static function install(Schema $schema)
    {
        if (!$schema->hasTable('gc_instance')) {
            $instance = $schema->createTable('gc_instance');
            $instance->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
            $instance->addColumn('title', 'string');
            $instance->addColumn('status', 'smallint');
            $instance->addColumn('is_primary', 'smallint');
            $instance->addColumn('version', 'string');
            $instance->addColumn('data', 'blob');
            $instance->addColumn('timestamp', 'integer', ['unsigned' => true]);
            $instance->addColumn('created', 'integer', ['unsigned' => true]);
            $instance->setPrimaryKey(['id']);
            $instance->addIndex(['title']);
            $instance->addIndex(['status']);
            $instance->addIndex(['is_primary']);
            $instance->addIndex(['timestamp']);
            $instance->addIndex(['created']);
        }

        if (!$schema->hasTable('gc_domain')) {
            $domain = $schema->createTable('gc_domain');
            $domain->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
            $domain->addColumn('title', 'string');
            $domain->setPrimaryKey(['id']);
            $domain->addIndex(['title'], 'index_title');
        }
    }
}
