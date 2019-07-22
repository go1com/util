<?php

namespace go1\util\schema;

use Doctrine\DBAL\Schema\Schema;

class CollectionSchema
{
    public static function install(Schema $schema)
    {
        if (!$schema->hasTable('collection_collection')) {
            $collection = $schema->createTable('collection_collection');

            $collection->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
            $collection->addColumn('type', 'string');
            $collection->addColumn('title', 'string');
            $collection->addColumn('portal_id', 'integer');
            $collection->addColumn('author_id', 'integer');
            $collection->addColumn('machine_name', 'string');
            $collection->addColumn('data', 'blob');
            $collection->addColumn('timestamp', 'integer');
            $collection->addColumn('created', 'integer');
            $collection->addColumn('updated', 'integer');
            $collection->addColumn('status', 'integer');

            $collection->setPrimaryKey(['id']);
            $collection->addUniqueIndex(['machine_name', 'portal_id']);
            $collection->addIndex(['type']);
            $collection->addIndex(['title']);
            $collection->addIndex(['portal_id']);
            $collection->addIndex(['author_id']);
            $collection->addIndex(['timestamp']);
            $collection->addIndex(['created']);
            $collection->addIndex(['updated']);
        }

        if (!$schema->hasTable('collection_collection_item')) {
            $item = $schema->createTable('collection_collection_item');

            $item->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
            $item->addColumn('collection_id', 'integer');
            $item->addColumn('lo_id', 'integer');
            $item->addColumn('timestamp', 'integer');

            $item->setPrimaryKey(['id']);
            $item->addUniqueIndex(['collection_id', 'lo_id']);
            $item->addIndex(['collection_id']);
            $item->addIndex(['lo_id']);
            $item->addIndex(['timestamp']);
        }

        if (!$schema->hasTable('collection_group_selection')) {
            $item = $schema->createTable('collection_group_selection');

            $item->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
            $item->addColumn('group_id', 'integer');
            $item->addColumn('collection_id', 'integer');
            $item->addColumn('custom', 'integer', ['default' => 0]);
            $item->addColumn('timestamp', 'integer');

            $item->setPrimaryKey(['id']);
            $item->addUniqueIndex(['collection_id', 'group_id']);
            $item->addIndex(['group_id']);
            $item->addIndex(['collection_id']);
            $item->addIndex(['custom']);
            $item->addIndex(['timestamp']);
        }
    }
}
