<?php

namespace go1\util\plan;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;
use go1\clients\MqClient;
use go1\util\DB;
use go1\util\plan\event_publishing\PlanCreateEventEmbedder;
use go1\util\plan\event_publishing\PlanDeleteEventEmbedder;
use go1\util\plan\event_publishing\PlanUpdateEventEmbedder;
use go1\util\queue\Queue;

class PlanRepository
{
    private $db;
    private $queue;
    private $planCreateEventEmbedder;
    private $planUpdateEventEmbedder;
    private $planDeleteEventEmbedder;

    public function __construct(
        Connection $db,
        MqClient $queue,
        PlanCreateEventEmbedder $planCreateEventEmbedder,
        PlanUpdateEventEmbedder $planUpdateEventEmbedder,
        PlanDeleteEventEmbedder $planDeleteEventEmbedder
    ) {
        $this->db = $db;
        $this->queue = $queue;
        $this->planCreateEventEmbedder = $planCreateEventEmbedder;
        $this->planUpdateEventEmbedder = $planUpdateEventEmbedder;
        $this->planDeleteEventEmbedder = $planDeleteEventEmbedder;
    }

    public static function install(Schema $schema)
    {
        if (!$schema->hasTable('gc_plan')) {
            $plan = $schema->createTable('gc_plan');
            $plan->addOption('description', 'GO1P-10732: Store learn-planning object.');
            $plan->addColumn('id', Type::INTEGER, ['unsigned' => true, 'autoincrement' => true]);
            $plan->addColumn('type', Type::SMALLINT, ['default' => PlanTypes::ASSIGN]);
            $plan->addColumn('user_id', Type::INTEGER, ['unsigned' => true]);
            $plan->addColumn('assigner_id', Type::INTEGER, ['unsigned' => true, 'notnull' => false]);
            $plan->addColumn('instance_id', Type::INTEGER, ['unsigned' => true, 'notnull' => false]);
            $plan->addColumn('entity_type', Type::STRING);
            $plan->addColumn('entity_id', Type::INTEGER, ['unsigned' => true]);
            $plan->addColumn('status', Type::INTEGER);
            $plan->addColumn('created_date', Type::DATETIME);
            $plan->addColumn('due_date', Type::DATETIME, ['notnull' => false]);
            $plan->addColumn('data', 'blob', ['notnull' => false]);
            $plan->setPrimaryKey(['id']);
            $plan->addIndex(['type']);
            $plan->addIndex(['user_id']);
            $plan->addIndex(['assigner_id']);
            $plan->addIndex(['instance_id']);
            $plan->addIndex(['entity_type', 'entity_id']);
            $plan->addIndex(['status']);
            $plan->addIndex(['created_date']);
            $plan->addIndex(['due_date']);
        }

        if (!$schema->hasTable('gc_plan_revision')) {
            $planRevision = $schema->createTable('gc_plan_revision');
            $planRevision->addColumn('id', Type::INTEGER, ['unsigned' => true, 'autoincrement' => true]);
            $planRevision->addColumn('type', Type::SMALLINT);
            $planRevision->addColumn('plan_id', Type::INTEGER, ['unsigned' => true]);
            $planRevision->addColumn('user_id', Type::INTEGER, ['unsigned' => true]);
            $planRevision->addColumn('assigner_id', Type::INTEGER, ['unsigned' => true, 'notnull' => false]);
            $planRevision->addColumn('instance_id', Type::INTEGER, ['unsigned' => true, 'notnull' => false]);
            $planRevision->addColumn('entity_type', Type::STRING);
            $planRevision->addColumn('entity_id', Type::INTEGER, ['unsigned' => true]);
            $planRevision->addColumn('status', Type::INTEGER);
            $planRevision->addColumn('created_date', Type::DATETIME);
            $planRevision->addColumn('due_date', Type::DATETIME, ['notnull' => false]);
            $planRevision->addColumn('data', Type::BLOB, ['notnull' => false]);
            $planRevision->setPrimaryKey(['id']);
            $planRevision->addIndex(['type']);
            $planRevision->addIndex(['plan_id']);
            $planRevision->addIndex(['user_id']);
            $planRevision->addIndex(['assigner_id']);
            $planRevision->addIndex(['instance_id']);
            $planRevision->addIndex(['entity_type', 'entity_id']);
            $planRevision->addIndex(['status']);
            $planRevision->addIndex(['created_date']);
            $planRevision->addIndex(['due_date']);
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

    public function loadByEntity(string $entityType, int $entityId, int $status = null, $type = PlanTypes::ASSIGN)
    {
        $q = $this->db->createQueryBuilder();
        $q
            ->select('*')
            ->from('gc_plan')
            ->where($q->expr()->eq('entity_type', ':entityType'))
            ->andWhere($q->expr()->eq('entity_id', ':entityId'))
            ->andWhere($q->expr()->eq('type', ':type'));
        !is_null($status) && $q
            ->andWhere($q->expr()->eq('status', ':status'));

        $q = $q->setParameters([
            ':entityType' => $entityType,
            ':entityId'   => $entityId,
            ':type'       => $type,
            ':status'     => $status,
        ])->execute();

        $plans = [];
        while ($plan = $q->fetch(DB::OBJ)) {
            $plans[] = Plan::create($plan);
        }

        return $plans;
    }

    public function loadRevisions(int $planId)
    {
        $q = $this->db
            ->createQueryBuilder()
            ->select('*')
            ->from('gc_plan_revision')
            ->where('plan_id = :planId')
            ->setParameter(':planId', $planId)
            ->execute();

        $revisions = [];
        while ($revision = $q->fetch(DB::OBJ)) {
            $revision->data = $revision->data ? json_decode($revision->data) : $revision->data;
            $revisions[] = $revision;
        }

        return $revisions;
    }

    public function create(Plan &$plan, bool $notify = false, array $queueContext = [])
    {
        $this->db->insert('gc_plan', [
            'type'         => $plan->type,
            'user_id'      => $plan->userId,
            'assigner_id'  => $plan->assignerId,
            'instance_id'  => $plan->instanceId,
            'entity_type'  => $plan->entityType,
            'entity_id'    => $plan->entityId,
            'status'       => $plan->status,
            'created_date' => $plan->created ? $plan->created->format(DATE_ISO8601) : '',
            'due_date'     => $plan->due ? $plan->due->format(DATE_ISO8601) : null,
            'data'         => $plan->data ? json_encode($plan->data) : null,
        ]);

        $plan->id = $this->db->lastInsertId('gc_plan');
        $plan->notify = $notify ?: ($queueContext['notify'] ?? false);
        $queueContext['notify'] = $plan->notify;

        $payload = $plan->jsonSerialize();
        $payload['embedded'] = $this->planCreateEventEmbedder->embedded($plan);
        $this->queue->publish($payload, Queue::PLAN_CREATE, $queueContext);

        return $plan->id;
    }

    public function createRevision(Plan &$plan)
    {
        $this->db->insert('gc_plan_revision', [
            'plan_id'      => $plan->id,
            'type'         => $plan->type,
            'user_id'      => $plan->userId,
            'assigner_id'  => $plan->assignerId,
            'instance_id'  => $plan->instanceId,
            'entity_type'  => $plan->entityType,
            'entity_id'    => $plan->entityId,
            'status'       => $plan->status,
            'created_date' => $plan->created ? $plan->created->format(DATE_ISO8601) : '',
            'due_date'     => $plan->due ? $plan->due->format(DATE_ISO8601) : '',
            'data'         => $plan->data ? json_encode($plan->data) : null,
        ]);
    }

    public function update(Plan $original, Plan $plan, bool $notify = false)
    {
        if (!$diff = $original->diff($plan)) {
            return null;
        }

        $this->db->transactional(function () use ($original, $plan, $notify, $diff) {
            $this->createRevision($original);
            $this->db->update('gc_plan', $diff, ['id' => $original->id]);
            $plan->id = $original->id;
            $plan->original = $original;
            $plan->notify = $notify;

            $payload = $plan->jsonSerialize();
            $payload['embedded'] = $this->planDeleteEventEmbedder->embedded($plan);
            $this->queue->publish($payload, Queue::PLAN_UPDATE);
        });
    }

    public function delete(int $id)
    {
        if (!$plan = $this->load($id)) {
            return null;
        }

        $this->db->delete('gc_plan', ['id' => $id]);

        $payload = $plan->jsonSerialize();
        $payload['embedded'] = $this->planDeleteEventEmbedder->embedded($plan);
        $this->queue->publish($payload, Queue::PLAN_DELETE);
    }

    public function merge(Plan $plan, bool $notify = false, array $queueContext = [])
    {
        $qb = $this->db->createQueryBuilder();
        $original = $qb
            ->select('*')
            ->from('gc_plan', 'p')
            ->where($qb->expr()->eq('type', ':type'))
            ->andWhere($qb->expr()->eq('user_id', ':userId'))
            ->andWhere($qb->expr()->eq('instance_id', ':instanceId'))
            ->andWhere($qb->expr()->eq('entity_type', ':entityType'))
            ->andWhere($qb->expr()->eq('entity_id', ':entityId'))
            ->setParameters([
                ':type'       => $plan->type,
                ':userId'     => $plan->userId,
                ':instanceId' => $plan->instanceId,
                ':entityType' => $plan->entityType,
                ':entityId'   => $plan->entityId,
            ])
            ->execute()
            ->fetch(DB::OBJ);

        if ($original) {
            $original = Plan::create($original);
            if (false === $plan->due) {
                $plan->due = $original->due;
            }
            $this->update($original, $plan, $notify);
            $planId = $original->id;
        } else {
            $planId = $this->create($plan, $notify, $queueContext);
        }

        return $planId;
    }

    public function archive(int $planId)
    {
        if (!$plan = $this->load($planId)) {
            return false;
        }

        $this->db->transactional(function () use ($plan) {
            $this->db->delete('gc_plan', ['id' => $plan->id]);
            $this->createRevision($plan);

            $payload = $plan->jsonSerialize();
            $payload['embedded'] = $this->planDeleteEventEmbedder->embedded($plan);
            $this->queue->publish($payload, Queue::PLAN_DELETE);
        });

        return true;
    }

    public function loadSuggestedPlan(string $entityType, int $entityId, int $userId): ?Plan
    {
        $q = $this->db->createQueryBuilder();
        $plan = $q
            ->select('*')
            ->from('gc_plan')
            ->where('type = :type')
            ->andWhere('entity_type = :entityType')
            ->andWhere('entity_id = :entityId')
            ->andWhere('user_id = :userId')
            ->setParameters([
                ':type'       => PlanTypes::SUGGESTED,
                ':entityType' => $entityType,
                ':entityId'   => $entityId,
                ':userId'     => $userId,
            ])
            ->execute()
            ->fetch(DB::OBJ);

        return $plan ? Plan::create($plan) : null;
    }
}
