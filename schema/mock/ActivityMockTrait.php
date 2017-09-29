<?php

namespace go1\util\schema\mock;

use Doctrine\DBAL\Connection;

trait ActivityMockTrait
{
    protected function createActivity(Connection $db, array $options = [])
    {
        $data = $options['data'] ?? [];
        $data = !$data ? json_encode(null) : (is_scalar($data) ? $data : json_encode($data));

        $db->insert('activity', [
            'instance_id' => $options['instance_id'] ?? 1,
            'user_id'     => $options['user_id'] ?? 1,
            'actor_id'    => $options['actor_id'] ?? 1,
            'action_id'   => $options['action_id'] ?? 1,
            'entity_type' => $options['entity_type'] ?? 'lo',
            'entity_id'   => $options['entity_id'] ?? 1,
            'created'     => isset($options['created']) ? $options['created'] : time(),
            'updated'     => isset($options['updated']) ? $options['updated'] : time(),
            'data'        => $data,
        ]);
        $id = $db->lastInsertId('activity');
        return $id;
    }
}
