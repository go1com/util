<?php

namespace go1\util\user;

use Doctrine\DBAL\Connection;
use go1\clients\MqClient;
use go1\util\edge\EdgeHelper;
use go1\util\edge\EdgeTypes;

class ManagerHelper
{
    public static function link(Connection $db, MqClient $queue, int $instanceId, int $managerId, int $userId)
    {
        EdgeHelper::link($db, $queue, EdgeTypes::HAS_MANAGER, $userId, $managerId, $instanceId);
    }

    public static function isManagerOf(Connection $db, string $instanceId, int $managerId, int $userId): bool
    {
        $found = 'SELECT 1 FROM gc_ro WHERE type = ? AND source_id = ? AND target_id = ? AND weight = ?';
        $found = $db->fetchColumn($found, [EdgeTypes::HAS_MANAGER, $userId, $managerId, $instanceId]);

        return $found;
    }
}
