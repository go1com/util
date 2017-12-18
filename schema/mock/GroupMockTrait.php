<?php

namespace go1\util\schema\mock;

use Doctrine\DBAL\Connection;
use go1\util\group\GroupAssignStatuses;
use go1\util\group\GroupItemStatus;
use go1\util\group\GroupStatus;
use go1\util\group\GroupTypes;

trait GroupMockTrait
{
    public function createGroup(Connection $db, array $options = [])
    {
        $db->insert('social_group', [
            'id'          => $options['id'] ?? null,
            'user_id'     => $title = isset($options['user_id']) ? $options['user_id'] : 1,
            'title'       => isset($options['title']) ? $options['title'] : 'Group Foo',
            'visibility'  => isset($options['visibility']) ? $options['visibility'] : GroupStatus::PUBLIC,
            'type'        => isset($options['type']) ? $options['type'] : GroupTypes::DEFAULT,
            'instance_id' => isset($options['instance_id']) ? $options['instance_id'] : 1,
            'created'     => isset($options['created']) ? $options['created'] : time(),
            'updated'     => isset($options['updated']) ? $options['updated'] : time(),
            'data'        => isset($options['data']) ? json_encode($options['data']) : '',
        ]);

        return $db->lastInsertId('social_group');
    }

    public function createGroupItem(Connection $db, array $options = [])
    {
        $db->insert('social_group_item', [
            'id'          => $options['id'] ?? null,
            'group_id'    => $title = isset($options['group_id']) ? $options['group_id'] : 1,
            'entity_type' => isset($options['entity_type']) ? $options['entity_type'] : 'user',
            'entity_id'   => isset($options['entity_id']) ? $options['entity_id'] : 1,
            'status'      => isset($options['status']) ? $options['status'] : GroupItemStatus::ACTIVE,
            'created'     => isset($options['created']) ? $options['created'] : time(),
            'updated'     => isset($options['updated']) ? $options['updated'] : time(),
        ]);

        return $db->lastInsertId('social_group_item');
    }

    public function createGroupAssign(Connection $db, array $options = [])
    {
        $data = isset($options['data']) ? (is_scalar($options['data']) ? json_decode($options['data'], true) : $options['data']) : null;
        $data = $data ? json_encode($data) : $data;

        $db->insert('social_group_assign', [
            'id'          => $options['id'] ?? null,
            'group_id'    => $options['group_id'],
            'instance_id' => $options['instance_id'],
            'entity_type' => $options['entity_type'],
            'entity_id'   => $options['entity_id'],
            'user_id'     => $options['user_id'],
            'status'      => isset($options['status']) ? $options['status'] : GroupAssignStatuses::PUBLISHED,
            'created'     => isset($options['created']) ? $options['created'] : time(),
            'updated'     => isset($options['updated']) ? $options['updated'] : time(),
            'due_date'    => isset($options['due_date']) ? $options['due_date'] : null,
            'data'        => $data,
        ]);
    }
}
