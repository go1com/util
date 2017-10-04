<?php

namespace go1\util\plan;

use Doctrine\DBAL\Connection;
use go1\util\DB;
use PDO;

class PlanHelper
{
    public static function loadByEntityAndUser(Connection $db, string $entityType, int $entityId, int $userId, int $status = PlanStatuses::ASSIGNED)
    {
        return $db
            ->executeQuery('SELECT * FROM gc_plan WHERE entity_type = ? AND entity_id = ? AND user_id = ? AND status = ?', [$entityType, $entityId, $userId, $status])
            ->fetch(DB::OBJ);
    }

    public static function loadByEntityAndAssigner(Connection $db, string $entityType, int $entityId, int $assignerId, int $status = PlanStatuses::ASSIGNED)
    {
        return $db
            ->executeQuery('SELECT * FROM gc_plan WHERE entity_type = ? AND entity_id = ? AND assigner_id = ? AND status = ?', [$entityType, $entityId, $assignerId, $status])
            ->fetch(DB::OBJ);
    }

    public static function userPlanIds(Connection $db, string $entityType, int $userId, int $status = PlanStatuses::ASSIGNED): array
    {
        return $db
            ->executeQuery('SELECT id FROM gc_plan WHERE entity_type = ? AND user_id = ? AND status = ?', [$entityType, $userId, $status])
            ->fetchAll(PDO::FETCH_COLUMN);
    }
}
