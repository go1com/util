<?php

namespace go1\util\plan;

use Doctrine\DBAL\Connection;
use go1\util\DB;

class PlanHelper
{
    public static function loadByEntityUserAndStatus(Connection $db, string $entityType, int $entityId, int $userId, int $status = Plan::STATUS_ASSIGNED)
    {
        return $db
            ->executeQuery('SELECT * FROM gc_plan WHERE entity_type = ? AND entity_id = ? AND user_id = ? AND status = ?', [$entityType, $entityId, $userId, $status])
            ->fetch(DB::OBJ);
    }
}
