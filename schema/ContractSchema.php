<?php

namespace go1\util\schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;

class ContractSchema
{
    public static function install(Schema $schema)
    {
        if (!$schema->hasTable('contract')) {
            $contract = $schema->createTable('contract');
            $contract->addColumn('id', Type::INTEGER, ['unsigned' => true, 'autoincrement' => true]);
            $contract->addColumn('instance_id', Type::INTEGER, ['unsigned' => true]);
            $contract->addColumn('user_id', Type::INTEGER, ['unsigned' => true]);
            $contract->addColumn('start_date', 'datetime', ['notnull' => false]);
            $contract->addColumn('signed_date', 'datetime', ['notnull' => false]);
            $contract->addColumn('initial_term', Type::STRING, ['notnull' => false]);
            $contract->addColumn('number_users', Type::INTEGER, ['unsigned' => true, 'notnull' => false]);
            $contract->addColumn('price', 'float', ['notnull' => false]);
            $contract->addColumn('tax', 'float', ['notnull' => false]);
            $contract->addColumn('tax_included', Type::BOOLEAN, ['notnull' => false]);
            $contract->addColumn('currency', Type::STRING, ['notnull' => false]);
            $contract->addColumn('payment_method', Type::STRING, ['notnull' => false]);
            $contract->addColumn('cancel_date', 'datetime', ['notnull' => false]);
            $contract->addColumn('data', Type::BLOB, ['notnull' => false]);
            $contract->addColumn('created', Type::INTEGER, ['unsigned' => true]);
            $contract->addColumn('updated', Type::INTEGER, ['unsigned' => true]);

            $contract->setPrimaryKey(['id']);
            $contract->addIndex(['instance_id']);
            $contract->addIndex(['user_id']);
            $contract->addIndex(['initial_term']);
            $contract->addIndex(['created']);
            $contract->addIndex(['updated']);
        }
    }
}
