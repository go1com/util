<?php

namespace go1\util\plan;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use go1\util\DB;

class PlanRepository
{
    private $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    public static function install(Schema $schema)
    {
        $table = $schema->createTable('gc_plan');
        $table->addOption('description', 'GO1P-10732: Store learn-planning object.');
        $table->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
        $table->addColumn('user_id', 'integer', ['unsigned' => true]);
        $table->addColumn('assigner_id', 'integer', ['unsigned' => true, 'notnull' => false]);
        $table->addColumn('entity_type', 'string');
        $table->addColumn('entity_id', 'integer');
        $table->addColumn('status', 'integer');
        $table->addColumn('created_date', 'datetime');
        $table->addColumn('due_date', 'datetime', ['notnull' => false]);
        $table->addColumn('data', 'blob', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['user_id']);
        $table->addIndex(['assigner_id']);
        $table->addIndex(['entity_type', 'entity_id']);
        $table->addIndex(['status']);
        $table->addIndex(['created_date']);
        $table->addIndex(['due_date']);
    }

    public function load(int $id)
    {
        $plan = $this
            ->db
            ->executeQuery('SELECT * FROM gc_plan WHERE id = ?', [$id])
            ->fetch(DB::OBJ);

        if ($plan) {
            $plan = Plan::create($plan);
        }

        return $plan;
    }

    public function create(Plan &$plan)
    {
        $this->db->insert('gc_plan', [
            'user_id'      => $plan->userId,
            'assigner_id'  => $plan->assignerId,
            'entity_type'  => $plan->entityType,
            'entity_id'    => $plan->entityId,
            'status'       => $plan->status,
            'created_date' => $plan->created ? $plan->created->format(DATE_ISO8601) : '',
            'due_date'     => $plan->due ? $plan->due->format(DATE_ISO8601) : '',
            'data'         => $plan->data ? json_encode($plan->data) : null,
        ]);

        return $plan->id = $this->db->lastInsertId('gc_plan');
    }
}
