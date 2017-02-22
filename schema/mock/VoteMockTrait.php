<?php

namespace go1\util\schema\mock;

use Doctrine\DBAL\Connection;

trait VoteMockTrait
{
    protected function createVote(Connection $db, $type, $entityType, $entityId, $profileId, $value)
    {
        $db->insert('vote_items', [
            'type'        => $type,
            'entity_type' => $entityType,
            'entity_id'   => $entityId,
            'profile_id'  => $profileId,
            'value'       => $value,
            'timestamp'   => time(),
        ]);

        return $db->lastInsertId('vote_items');
    }
}
