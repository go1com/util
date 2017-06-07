<?php

namespace go1\util\plan;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;
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
        if (!$schema->hasTable('gc_plan')) {
            $plan = $schema->createTable('gc_plan');
            $plan->addOption('description', 'GO1P-10732: Store learn-planning object.');
            $plan->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
            $plan->addColumn('user_id', 'integer', ['unsigned' => true]);
            $plan->addColumn('assigner_id', 'integer', ['unsigned' => true, 'notnull' => false]);
            $plan->addColumn('instance_id', Type::INTEGER, ['unsigned' => true, 'notnull' => false]);
            $plan->addColumn('entity_type', 'string');
            $plan->addColumn('entity_id', 'integer');
            $plan->addColumn('status', 'integer');
            $plan->addColumn('created_date', 'datetime');
            $plan->addColumn('due_date', 'datetime', ['notnull' => false]);
            $plan->addColumn('data', 'blob', ['notnull' => false]);
            $plan->setPrimaryKey(['id']);
            $plan->addIndex(['user_id']);
            $plan->addIndex(['assigner_id']);
            $plan->addIndex(['instance_id']);
            $plan->addIndex(['entity_type', 'entity_id']);
            $plan->addIndex(['status']);
            $plan->addIndex(['created_date']);
            $plan->addIndex(['due_date']);
        }
    }

    public function load(int $id)
    {
        $plan = $this
            ->db
            ->executeQuery('SELECT * FROM gc_plan WHERE id = ?', [$id])
            ->fetch(DB::OBJ);

        return $plan ? Plan::create($plan) : false;
    }

    public function loadMultiple(array $ids)
    {
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
