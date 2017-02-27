<?php

namespace go1\util\schema\mock;

use Doctrine\DBAL\Connection;
use go1\util\GroupItemStatus;
use go1\util\GroupStatus;

trait GroupMockTrait
{
    public function createGroup(Connection $db, array $options = [])
    {
        $db->insert('social_group', [
            'user_id'       => $title = isset($options['user_id']) ? $options['user_id'] : 1,
            'title'         => isset($options['title']) ? $options['title'] : 'Group Foo',
            'visibility'    => isset($options['visibility']) ? $options['visibility'] : GroupStatus::PUBLIC,
            'instance_id'   => isset($options['instance_id']) ? $options['instance_id'] : 1,
            'created'       => isset($options['created']) ? $options['created'] : time(),
            'updated'       => isset($options['updated']) ? $options['updated'] : time(),
        ]);

        return $db->lastInsertId('social_group');
    }

    public function createGroupItem(Connection $db, array $options = [])
    {
        $db->insert('social_group_item', [
            'group_id'      => $title = isset($options['group_id']) ? $options['group_id'] : 1,
            'entity_type'   => isset($options['entity_type']) ? $options['entity_type'] : 'user',
            'entity_id'     => isset($options['entity_id']) ? $options['entity_id'] : 1,
            'status'        => isset($options['status']) ? $options['status'] : GroupItemStatus::ACTIVE,
            'created'       => isset($options['created']) ? $options['created'] : time(),
            'updated'       => isset($options['updated']) ? $options['updated'] : time(),
        ]);

        return $db->lastInsertId('social_group_item');
    }
}
