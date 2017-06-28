<?php

namespace go1\util\schema;

use Doctrine\DBAL\Schema\Schema;

class SubscriptionSchema
{
    public static function install(Schema $schema)
    {
        if (!$schema->hasTable('subscription_plan')) {
            $plan = $schema->createTable('subscription_plan');
            $plan->addColumn('uuid', 'guid');
            $plan->addColumn('instance', 'string');
            $plan->addColumn('name', 'string');
            $plan->addColumn('data', 'text', ['description' => 'Plan object on remote service.', 'notnull' => false]);
            $plan->setPrimaryKey(['uuid']);
            $plan->addUniqueIndex(['instance', 'name']);
        }

        if (!$schema->hasTable('subscription_customer')) {
            $customer = $schema->createTable('subscription_customer');
            $customer->addColumn('uuid', 'guid');
            $customer->addColumn('user_id', 'integer', ['unsigned' => true]);
            $customer->addColumn('mail', 'string');
            $customer->addColumn('data', 'text');
            $customer->setPrimaryKey(['uuid']);
            $customer->addIndex(['user_id']);
            $customer->addIndex(['mail']);
        }

        if (!$schema->hasTable('subscription_recurring')) {
            $recurring = $schema->createTable('subscription_recurring');
            $recurring->addColumn('uuid', 'guid');
            $recurring->addColumn('user_id', 'integer', ['unsigned' => true]);
            $recurring->addColumn('plan_uuid', 'guid');
            $recurring->addColumn('status', 'smallint', []);
            $recurring->addColumn('created', 'integer', ['unsigned' => true]);
            $recurring->addColumn('timestamp', 'integer', ['unsigned' => true]);
            $recurring->addColumn('period_end', 'integer', ['unsigned' => true]);
            $recurring->addColumn('data', 'text', ['notnull' => false]);
            $recurring->setPrimaryKey(['uuid']);
            $recurring->addUniqueIndex(['user_id', 'plan_uuid']);
            $recurring->addIndex(['user_id']);
            $recurring->addIndex(['plan_uuid']);
            $recurring->addIndex(['status']);
            $recurring->addIndex(['period_end']);
            $recurring->addIndex(['timestamp']);
            $recurring->addForeignKeyConstraint('subscription_plan', ['plan_uuid'], ['uuid']);
        }
    }
}
