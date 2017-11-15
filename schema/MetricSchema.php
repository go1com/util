<?php

namespace go1\util\schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;

class MetricSchema
{
    public static function install(Schema $schema)
    {
        if (!$schema->hasTable('staff_metric')) {
            $metric = $schema->createTable('staff_metric');
            $metric->addColumn('id', Type::INTEGER, ['unsigned' => true, 'autoincrement' => true]);
            $metric->addColumn('title', Type::STRING);
            $metric->addColumn('type', Type::SMALLINT);
            $metric->addColumn('status', Type::SMALLINT);
            $metric->addColumn('value', Type::FLOAT);
            $metric->addColumn('user_id', Type::INTEGER, ['unsigned' => true]);
            $metric->addColumn('start_date', Type::DATETIME, ['notnull' => false]);
            $metric->addColumn('description', Type::TEXT, ['notnull' => false]);
            $metric->addColumn('created', Type::INTEGER, ['unsigned' => true]);
            $metric->addColumn('updated', Type::INTEGER, ['unsigned' => true]);
            $metric->setPrimaryKey(['id']);
            $metric->addIndex(['type']);
            $metric->addIndex(['value']);
            $metric->addIndex(['user_id']);
            $metric->addIndex(['start_date']);
            $metric->addIndex(['created']);
            $metric->addIndex(['updated']);
        }
    }
}
