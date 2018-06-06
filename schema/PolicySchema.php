<?php

namespace go1\util\schema;

use Doctrine\DBAL\Schema\Schema;

class PolicySchema
{
    public static function install(Schema $schema)
    {
        if (!$schema->hasTable('policy_policy_item')) {
            $item = $schema->createTable('policy_policy_item');

            $item->addColumn('id', 'string');
            $item->addColumn('type', 'integer');
            $item->addColumn('portal_id', 'integer');
            $item->addColumn('host_entity_type', 'string');
            $item->addColumn('host_entity_id', 'integer');
            $item->addColumn('entity_type', 'string');
            $item->addColumn('entity_id', 'integer');
            $item->addColumn('created', 'integer');
            $item->addColumn('updated', 'integer');

            $item->setPrimaryKey(['id']);
            $item->addIndex(['type']);
            $item->addIndex(['portal_id']);
            $item->addIndex(['host_entity_type']);
            $item->addIndex(['host_entity_id']);
            $item->addIndex(['entity_type']);
            $item->addIndex(['entity_id']);
            $item->addIndex(['created']);
            $item->addIndex(['updated']);
        }
    }
}
