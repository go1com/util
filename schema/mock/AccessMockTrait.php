<?php

namespace go1\util\schema\mock;


use Doctrine\DBAL\Connection;

trait AccessMockTrait
{
    public function createAccess(Connection $db, array $options = [])
    {
        $db->insert('gc_access', [
            'group_id'    => isset($options['group_id']) ? $options['group_id'] : 0,
            'instance_id' => isset($options['instance_id']) ? $options['instance_id'] : 0,
            'entity_type' => isset($options['entity_type']) ? $options['entity_type'] : 'plan',
            'entity_id'   => isset($options['entity_id']) ? $options['entity_id'] : 0,
            'user_id'     => isset($options['user_id']) ? $options['user_id'] : 0,
        ]);
    }
}
