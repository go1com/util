<?php

namespace go1\util\schema\mock;

use Doctrine\DBAL\Connection;
use go1\util\group\GroupItemStatus;
use go1\util\group\GroupStatus;
use go1\util\note\NoteCommentStatus;

trait NoteMockTrait
{
    protected function createNote(Connection $db, array $options)
    {
        $data = isset($options['data']) ? (is_scalar($options['data']) ? json_decode($options['data'], true) : $options['data']) : [];
        $db->insert('gc_note', [
            'entity_id'     => !empty($options['entity_id']) ? $options['entity_id'] : 1,
            'profile_id'    => !empty($options['profile_id']) ? $options['profile_id'] : 1,
            'uuid'          => !empty($options['uuid']) ? $options['uuid'] : 'NOTE_UUID',
            'instance_id'   => !empty($options['instance_id']) ? $options['instance_id'] : null,
            'created'       => !empty($options['created']) ? $options['created'] : time(),
            'entity_type'   => !empty($options['entity_type']) ? $options['entity_type'] : 'lo',
            'private'       => !empty($options['private']) ? $options['private'] : 0,
            'description'   => !empty($options['description']) ? $options['description'] : null,
            'data'          => json_encode($data),
        ]);

        return $db->lastInsertId('gc_note');
    }

    protected function createNoteComment(Connection $db, array $options)
    {
        $db->insert('note_comment', [
            'note_id'     => !empty($options['note_id']) ? $options['note_id'] : 1,
            'user_id'     => !empty($options['user_id']) ? $options['user_id'] : 1,
            'status'      => !empty($options['status']) ? $options['status'] : NoteCommentStatus::ENABLED,
            'created'     => !empty($options['created']) ? $options['created'] : time(),
            'updated'     => !empty($options['updated']) ? $options['updated'] : time(),
            'description' => !empty($options['description']) ? $options['description'] : null,
        ]);

        return $db->lastInsertId('note_comment');
    }
}
