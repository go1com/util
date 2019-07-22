<?php

namespace go1\util\dimensions;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;
use go1\clients\MqClient;
use go1\util\DB;
use go1\util\plan\event_publishing\PlanCreateEventEmbedder;
use go1\util\plan\event_publishing\PlanDeleteEventEmbedder;
use go1\util\plan\event_publishing\PlanUpdateEventEmbedder;
use go1\util\queue\Queue;

class DimensionRepository
{
    private $db;

    public function __construct(Connection $db) {
        $this->db = $db;
    }

    public static function install(Schema $schema)
    {
        if (!$schema->hasTable('dimensions')) {
            $table = $schema->createTable('dimensions');
            $table->addColumn('id', TYPE::INTEGER, ['autoincrement' => true]);
            $table->setPrimaryKey(['id'], 'pk_id');
            $table->addColumn('parent_id', TYPE::INTEGER, ['notnull' => false]);
            $table->addColumn('name', TYPE::STRING);
            $table->addColumn('type', TYPE::STRING);
            $table->addColumn('created_date', TYPE::DATETIME);
            $table->addColumn('modified_date', TYPE::DATETIME);
            $table->addIndex(['type']);
            $table->addIndex(['name']);
        }
    }
}
