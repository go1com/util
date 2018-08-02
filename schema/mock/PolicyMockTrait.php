<?php

namespace go1\util\schema\mock;

use Doctrine\DBAL\Connection;
use go1\util\EntityTypes;
use go1\util\policy\Realm;
use go1\util\Text;

trait PolicyMockTrait
{
    protected function createItem(Connection $db, array $options): string
    {
        $db->insert('policy_policy_item', $record = [
            'id'               => $options['id'] ?? Text::uniqueId(),
            'type'             => $options['type'] ?? Realm::VIEW,
            'portal_id'        => $options['portal_id'] ?? 1,
            'host_entity_type' => $options['host_entity_type'] ?? EntityTypes::LO,
            'host_entity_id'   => $options['host_entity_id'] ?? 1,
            'entity_type'      => $options['entity_type'] ?? EntityTypes::USER,
            'entity_id'        => $options['entity_id'] ?? 1,
            'created'          => time(),
            'updated'          => time(),
        ]);

        return $record['id'];
    }
}
