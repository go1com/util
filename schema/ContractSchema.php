<?php

    namespace go1\util\schema;

    use Doctrine\DBAL\Schema\Schema;
    use Doctrine\DBAL\Types\Type;
    use go1\kv\KV;

    class ContractSchema
    {
        public static function install(Schema $schema)
        {
            if (!$schema->hasTable('contract')) {
                $contract = $schema->createTable('contract');
                $contract->addColumn('id', Type::INTEGER, ['unsigned' => true, 'autoincrement' => true]);
                $contract->addColumn('instance_id', Type::INTEGER, ['unsigned' => true]);
                $contract->addColumn('user_id', Type::INTEGER, ['unsigned' => true]);
                $contract->addColumn('staff_id', Type::INTEGER, ['unsigned' => true, 'notnull' => false]);
                $contract->addColumn('parent_id', Type::INTEGER, ['unsigned' => true, 'notnull' => false]);
                $contract->addColumn('status', Type::INTEGER);
                $contract->addColumn('name', Type::STRING, ['notnull' => false]);
                $contract->addColumn('start_date', Type::DATETIME, ['notnull' => false]);
                $contract->addColumn('signed_date', Type::DATETIME, ['notnull' => false]);
                $contract->addColumn('initial_term', Type::STRING, ['notnull' => false]);
                $contract->addColumn('number_users', Type::INTEGER, ['unsigned' => true, 'notnull' => false]);
                $contract->addColumn('price', Type::FLOAT, ['notnull' => false]);
                $contract->addColumn('tax', Type::FLOAT, ['notnull' => false]);
                $contract->addColumn('tax_included', Type::STRING, ['notnull' => false]);
                $contract->addColumn('currency', Type::STRING, ['notnull' => false]);
                $contract->addColumn('aud_net_amount', Type::FLOAT, ['notnull' => false]);
                $contract->addColumn('frequency', Type::STRING, ['notnull' => false]);
                $contract->addColumn('frequency_other', Type::STRING, ['notnull' => false]);
                $contract->addColumn('custom_term', Type::TEXT, ['notnull' => false]);
                $contract->addColumn('payment_method', Type::STRING, ['notnull' => false]);
                $contract->addColumn('renewal_date', Type::DATETIME, ['notnull' => false]);
                $contract->addColumn('cancel_date', Type::DATETIME, ['notnull' => false]);
                $contract->addColumn('data', Type::BLOB, ['notnull' => false]);
                $contract->addColumn('created', Type::INTEGER, ['unsigned' => true]);
                $contract->addColumn('updated', Type::INTEGER, ['unsigned' => true]);

                $contract->setPrimaryKey(['id']);
                $contract->addIndex(['instance_id']);
                $contract->addIndex(['user_id']);
                $contract->addIndex(['staff_id']);
                $contract->addIndex(['parent_id']);
                $contract->addIndex(['status']);
                $contract->addIndex(['initial_term']);
                $contract->addIndex(['created']);
                $contract->addIndex(['updated']);
            }

            if (!$schema->hasTable('contract_kv')) {
                KV::migrate($schema, 'contract_kv');
            }
        }
    }
