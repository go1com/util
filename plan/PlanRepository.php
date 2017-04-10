<?php

namespace go1\util\plan;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use go1\clients\MqClient;
use go1\util\DB;
use go1\util\Queue;

class PlanRepository
{
    private $db;
    private $queue;

    public function __construct(Connection $db, MqClient $queue)
    {
        $this->db = $db;
        $this->queue = $queue;
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

        return $plan ? Plan::create($plan) : false;
    }

    public function loadMultiple(array $ids) {
        $q = $this->db->createQueryBuilder();
        $q = $q
            ->select('*')
            ->from('gc_plan')
            ->where($q->expr()->in('id', ':ids'))
            ->setParameter(':ids', $ids, Connection::PARAM_INT_ARRAY)
            ->execute();

        $plans = [];
        while ($plan = $q->fetch(DB::OBJ)) {
            $plans[] = Plan::create($plan);
        }

        return $plans;
    }

    public function loadByEntity(string $entityType, int $entityId, int $status = null)
    {
        $q = $this->db->createQueryBuilder();
        $q
            ->select('*')
            ->from('gc_plan')
            ->where($q->expr()->eq('entity_type', ':entityType'))
            ->andWhere($q->expr()->eq('entity_id', ':entityId'));
        !is_null($status) && $q
            ->andWhere($q->expr()->eq('status', ':status'));

        $q = $q->setParameters([
            ':entityType' => $entityType,
            ':entityId'   => $entityId,
            ':status'     => $status,
        ])->execute();

        $plans = [];
        while ($plan = $q->fetch(DB::OBJ)) {
            $plans[] = Plan::create($plan);
        }

        return $plans;
    }

    public function create(Plan &$plan, bool $notify = false)
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

        $plan->id = $this->db->lastInsertId('gc_plan');
        $plan->notify = $notify;
        $this->queue->publish($plan, Queue::PLAN_CREATE);

        return $plan->id;
    }

    public function update(int $id, Plan $plan)
    {
        if (!$original = $this->load($id)) {
            return null;
        }

        if (!$diff = $original->diff($plan)) {
            return null;
        }

        $this->db->update('gc_plan', $diff, ['id' => $id]);
        $plan->original = $original;
        $this->queue->publish($plan, Queue::PLAN_UPDATE);
    }

    public function delete(int $id)
    {
        if (!$plan = $this->load($id)) {
            return null;
        }

        $this->db->delete('gc_plan', ['id' => $id]);
        $this->queue->publish($plan, Queue::PLAN_DELETE);
    }
}
