<?php

namespace go1\util\group;

use Doctrine\DBAL\Connection;
use go1\clients\MqClient;
use go1\util\DB;
use go1\util\queue\Queue;

class GroupAssignHelper
{
    public static function merge(
        Connection $db,
        MqClient $mqClient,
        int $groupId,
        int $instanceId,
        int $userId,
        string $entityType,
        int $entityId
    )
    {
        $keys = [
            'group_id'    => $groupId,
            'instance_id' => $instanceId,
            'entity_type' => $entityType,
            'entity_id'   => $entityId,
        ];
        $values = $keys + [
                'user_id'     => $userId,
                'status'      => GroupAssignStatuses::PUBLISHED,
                'updated'     => time(),
            ];

        $originalAssign = self::loadBy($db, $groupId, $instanceId, $entityType, $entityId);
        $values += $originalAssign ? [] : ['created' => time()];

        DB::merge($db, 'social_group_assign', $keys, $values);

        $assignId = $originalAssign->id ?? $db->lastInsertId('social_group_assign');
        $assign = GroupHelper::loadAssignment($db, $assignId);

        if ($originalAssign) {
            $assign->original = $originalAssign;

            $mqClient->publish($assign, Queue::GROUP_ASSIGN_UPDATE);
        }
        else {
            $mqClient->publish($assign, Queue::GROUP_ASSIGN_CREATE);
        }
    }

    public static function archive(
        Connection $db,
        MqClient $mqClient,
        int $groupId,
        int $instanceId,
        int $userId,
        string $entityType,
        int $entityId
    )
    {
        if ($assign = self::loadBy($db, $groupId, $instanceId, $entityType, $entityId)) {
            $db->update(
                'social_group_assign',
                ['user_id' => $userId, 'status' => GroupAssignStatuses::ARCHIVED],
                ['id' => $assign->id]
            );
            $mqClient->publish($assign, Queue::GROUP_ASSIGN_DELETE);
        }
    }

    public static function loadBy(
        Connection $db,
        int $groupId,
        int $instanceId,
        string $entityType,
        int $entityId
    )
    {
        return $db
            ->createQueryBuilder()
            ->select('*')
            ->from('social_group_assign', 'a')
            ->where('group_id = :groupId')
            ->andWhere('instance_id = :instanceId')
            ->andWhere('entity_type = :entityType')
            ->andWhere('entity_id = :entityId')
            ->setParameters([
                'groupId'    => $groupId,
                'instanceId' => $instanceId,
                'entityType' => $entityType,
                'entityId'   => $entityId,
            ])
            ->execute()
            ->fetch(DB::OBJ);
    }
}
