<?php

namespace go1\util\schema\mock;

use Doctrine\DBAL\Connection;
use go1\util\DateTime;
use go1\util\plan\PlanStatuses;
use go1\util\plan\PlanTypes;

trait PlanMockTrait
{
    protected function createPlan(Connection $db, array $options = [])
    {
        $db->insert('gc_plan', [
            'type'         => isset($options['type']) ? $options['type'] : PlanTypes::ASSIGN,
            'user_id'      => isset($options['user_id']) ? $options['user_id'] : 0,
            'assigner_id'  => isset($options['assigner_id']) ? $options['assigner_id'] : null,
            'instance_id'  => isset($options['instance_id']) ? $options['instance_id'] : 0,
            'entity_type'  => isset($options['entity_type']) ? $options['entity_type'] : PlanTypes::ENTITY_LO,
            'entity_id'    => isset($options['entity_id']) ? $options['entity_id'] : 0,
            'status'       => isset($options['status']) ? $options['status'] : PlanStatuses::ASSIGNED,
            'due_date'     => isset($options['due_date']) ? DateTime::create($options['due_date'])->format(DATE_ISO8601) : null,
            'created_date' => DateTime::create(isset($options['created_date']) ? $options['created_date'] : time())->format(DATE_ISO8601),
            'data'         => empty($options['data']) ? null : json_encode(is_scalar($options['data']) ? json_decode($options['data']) : $options['data']),
        ]);

        return $db->lastInsertId('gc_plan');
    }
}
