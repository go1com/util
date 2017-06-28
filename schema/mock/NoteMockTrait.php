<?php

namespace go1\util\schema\mock;

use Doctrine\DBAL\Connection;
use go1\util\group\GroupItemStatus;
use go1\util\group\GroupStatus;

trait NoteMockTrait
{
    protected function createNote(Connection $db, array $options)
    {
        $data = isset($options['data']) ? (is_scalar($options['data']) ? json_decode($options['data'], true) : $options['data']) : [];
        $db->insert('gc_note', [
            'entity_id'     => !empty($options['entity_id']) ? $options['entity_id'] : 1,
            'profile_id'    => !empty($options['profile_id']) ? $options['profile_id'] : 1,
            'uuid'          => !empty($options['uuid']) ? $options['uuid'] : 'NOTE_UUID',
            'created'       => !empty($options['created']) ? $options['created'] : time(),
            'entity_type'   => !empty($options['entity_type']) ? $options['entity_type'] : 'lo',
            'data'          => json_encode($data),
        ]);

        return $db->lastInsertId('gc_note');
    }
}
