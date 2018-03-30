<?php

namespace go1\util\group;

use Doctrine\DBAL\Connection;
use go1\clients\MqClient;
use go1\util\queue\Queue;

class GroupRepository
{
    private $db;
    private $queue;

    public function __construct(Connection $db, MqClient $queue)
    {
        $this->db = $db;
        $this->queue = $queue;
    }

    public function db(): Connection
    {
        return $this->db;
    }

    public function create(
        string $type,
        string $instanceId,
        string $title = '',
        bool $visibility = GroupStatus::PRIVATE,
        int $userId = 1,
        array $data = null)
    {
        $this->db->insert('social_group', $row = [
            'title'       => $title,
            'user_id'     => $userId,
            'instance_id' => $instanceId,
            'type'        => $type,
            'visibility'  => $visibility,
            'created'     => $time = time(),
            'updated'     => $time,
            'data'        => is_scalar($data) ? $data : json_encode($data),
        ]);

        $row['id'] = $this->db->lastInsertId('social_group');
        $this->queue->publish($row, Queue::GROUP_CREATE);

        return $row['id'];
    }

    public function createItem(int $groupId, string $entityType, int $entityId, int $status)
    {
        $this->db->insert('social_group_item', $row = [
            'group_id'    => $groupId,
            'entity_type' => $entityType,
            'entity_id'   => $entityId,
            'status'      => $status,
            'created'     => $time = time(),
            'updated'     => $time,
        ]);

        $row['id'] = $this->db->lastInsertId('social_group_item');
        $this->queue->publish($row, Queue::GROUP_ITEM_CREATE);

        return $row['id'];
    }

    public function removeItem(int $itemId)
    {
        if ($item = GroupHelper::loadItem($this->db, $itemId)) {
            $this->db->delete('social_group_item', ['id' => $itemId]);
            $this->queue->publish((array) $item, Queue::GROUP_ITEM_DELETE);
        }
    }
}
