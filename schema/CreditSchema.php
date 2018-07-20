<?php

namespace go1\util\schema;

use Doctrine\DBAL\Schema\Schema;

class CreditSchema
{
    public static function install(Schema $schema)
    {
        $credit = $schema->createTable('credit');
        $credit->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
        $credit->addColumn('owner_id', 'integer', ['unsigned' => true]);
        $credit->addColumn('portal_id', 'integer', ['unsigned' => true]);
        $credit->addColumn('portal_active_id', 'integer', ['unsigned' => true, 'notnull' => false]);
        $credit->addColumn('product_type', 'string', ['default' => 'lo']);
        $credit->addColumn('product_id', 'integer', ['unsigned' => true]);
        $credit->addColumn('created', 'integer', ['unsigned' => true]);
        $credit->addColumn('updated', 'integer', ['unsigned' => true]);
        $credit->addColumn('status', 'smallint', ['unsigned' => true]);
        $credit->addColumn('transaction_id', 'integer', ['unsigned' => true, 'notnull' => false]);
        $credit->addColumn('token', 'string');
        $credit->addColumn('privacy', 'smallint', ['unsigned' => true]);
        $credit->setPrimaryKey(['id']);
        $credit->addIndex(['portal_id']);
        $credit->addIndex(['owner_id']);
        $credit->addIndex(['product_type', 'product_id']);
        $credit->addIndex(['created']);
        $credit->addIndex(['updated']);
        $credit->addIndex(['status']);
        $credit->addIndex(['token']);
        $credit->addIndex(['privacy']);

        $usage = $schema->createTable('credit_usage');
        $usage->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
        $usage->addColumn('credit_id', 'integer', ['unsigned' => true]);
        $usage->addColumn('actor_id', 'integer', ['unsigned' => true]);
        $usage->addColumn('user_id', 'integer', ['unsigned' => true]);
        $usage->addColumn('transaction_id', 'integer', ['unsigned' => true, 'notnull' => false]);
        $usage->addColumn('created', 'integer', ['unsigned' => true]);
        $usage->setPrimaryKey(['id']);
        $usage->addUniqueIndex(['credit_id']);
        $usage->addUniqueIndex(['transaction_id']);
        $usage->addForeignKeyConstraint('credit', ['credit_id'], ['id']);
        $usage->addIndex(['credit_id']);
        $usage->addIndex(['actor_id']);
        $usage->addIndex(['created']);

        $audit = $schema->createTable('credit_audit');
        $audit->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
        $audit->addColumn('credit_id', 'integer', ['unsigned' => true]);
        $audit->addColumn('patch', 'text');
        $audit->addColumn('created', 'integer', ['unsigned' => true]);
        $audit->setPrimaryKey(['id']);
        $audit->addIndex(['created']);
        $audit->addForeignKeyConstraint('credit', ['credit_id'], ['id']);
    }
}
