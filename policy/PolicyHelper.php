<?php
namespace go1\util\policy;

use Doctrine\DBAL\Connection;
use go1\util\DB;
use go1\util\EntityTypes;

class PolicyHelper
{
    public static function entityRealmOnLO(Connection $policyDB, string $entityType, int $entityId, int $portalId, int $loId): ?int
    {
        $realm = $policyDB
            ->createQueryBuilder()
            ->select('type')
            ->from('policy_policy_item')
            ->where('portal_id = :portalId')
            ->andWhere('host_entity_type = :hostEntityType')
            ->andWhere('host_entity_id = :hostEntityId')
            ->andWhere('entity_type = :entityType')
            ->andWhere('entity_id = :entityId')
            ->setParameter('portalId', $portalId, DB::INTEGER)
            ->setParameter('hostEntityType', EntityTypes::LO, DB::STRING)
            ->setParameter('hostEntityId', $loId, DB::INTEGER)
            ->setParameter('entityType', $entityType, DB::STRING)
            ->setParameter('entityId', $entityId, DB::INTEGER)
            ->execute()
            ->fetch(DB::COL);

        return $realm ?: null;
    }

    public static function loadItem(Connection $policyDB, string $id):? PolicyItem
    {
        $policyItem = $policyDB
            ->executeQuery('SELECT * FROM policy_policy_item WHERE id = ?', [$id], [DB::STRING])
            ->fetch(DB::OBJ);

        return $policyItem ? PolicyItem::create($policyItem) : null;
    }
}
