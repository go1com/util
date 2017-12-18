<?php

namespace go1\util\plan;

use Doctrine\DBAL\Connection;
use go1\util\DB;
use PDO;

class PlanHelper
{
    public static function loadByEntityAndUser(Connection $db, string $entityType, int $entityId, int $userId, array $statuses = [PlanStatuses::ASSIGNED, PlanStatuses::SCHEDULED], $type = PlanTypes::ASSIGN)
    {
        return $db
            ->executeQuery(
                'SELECT * FROM gc_plan WHERE `type` = ? AND entity_type = ? AND entity_id = ? AND user_id = ? AND status IN (?)',
                [$type, $entityType, $entityId, $userId, $statuses],
                [DB::INTEGER, DB::STRING, DB::INTEGER, DB::INTEGER, DB::INTEGERS]
            )
            ->fetch(DB::OBJ);
    }

    public static function loadByEntityAndAssigner(Connection $db, string $entityType, int $entityId, int $assignerId, array $statuses = [PlanStatuses::ASSIGNED, PlanStatuses::SCHEDULED], $type = PlanTypes::ASSIGN)
    {
        return $db
            ->executeQuery(
                'SELECT * FROM gc_plan WHERE `type` = ? AND entity_type = ? AND entity_id = ? AND assigner_id = ? AND status IN (?)',
                [$type, $entityType, $entityId, $assignerId, $statuses],
                [DB::INTEGER, DB::STRING, DB::INTEGER, DB::INTEGER, DB::INTEGERS]
            )
            ->fetch(DB::OBJ);
    }

    public static function userPlanIds(Connection $db, string $entityType, int $userId, int $status = PlanStatuses::ASSIGNED, $type = PlanTypes::ASSIGN): array
    {
        return $db
            ->executeQuery('SELECT id FROM gc_plan WHERE `type` = ? AND entity_type = ? AND user_id = ? AND status = ?', [$type, $entityType, $userId, $status])
            ->fetchAll(PDO::FETCH_COLUMN);
    }

    public static function load(Connection $db, int $id)
    {
        return $db->executeQuery('SELECT * FROM gc_plan WHERE id = ?', [$id])->fetch(DB::OBJ);
    }

    public static function isVersion($data, $version)
    {
        $data = is_string($data) ? json_decode($data) : json_decode(json_encode($data));
        $checkVersion = $data->version ?? null;

        return $version == $checkVersion;
    }
}
