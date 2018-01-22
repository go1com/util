<?php

namespace go1\util\schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;

class NoteSchema
{
    public static function install(Schema $schema)
    {
        if (!$schema->hasTable('gc_note')) {
            $note = $schema->createTable('gc_note');
            $note->addColumn('id', Type::INTEGER, ['unsigned' => true, 'autoincrement' => true]);
            $note->addColumn('entity_id', Type::BIGINT, ['unsigned' => true]);
            $note->addColumn('entity_type', Type::STRING, ['notnull' => false, 'length' => 11, 'default' => 'lo']);
            $note->addColumn('profile_id', Type::INTEGER, ['unsigned' => true]);
            $note->addColumn('instance_id', Type::INTEGER, ['notnull' => false]);
            $note->addColumn('uuid', Type::STRING, ['notnull' => false, 'length' => 36]);
            $note->addColumn('created', Type::INTEGER, ['unsigned' => true, 'length' => 11]);
            $note->addColumn('private', Type::SMALLINT, ['default' => 0, 'length' => 2]);
            $note->addColumn('description', Type::TEXT, ['notnull' => false]);
            $note->addColumn('data', Type::BLOB);
            $note->setPrimaryKey(['id']);
            $note->addUniqueIndex(['uuid']);
            $note->addIndex(['entity_id']);
            $note->addIndex(['profile_id']);
            $note->addIndex(['instance_id']);
            $note->addIndex(['uuid']);
            $note->addIndex(['created']);
            $note->addIndex(['entity_type']);
            $note->addIndex(['private']);
        }

        if (!$schema->hasTable('note_comment')) {
            $comment = $schema->createTable('note_comment');
            $comment->addColumn('id', Type::INTEGER, ['unsigned' => true, 'autoincrement' => true]);
            $comment->addColumn('note_id', Type::BIGINT, ['unsigned' => true]);
            $comment->addColumn('user_id', Type::INTEGER, ['unsigned' => true]);
            $comment->addColumn('status', Type::SMALLINT, ['unsigned' => true]);
            $comment->addColumn('created', Type::INTEGER, ['unsigned' => true, 'length' => 11]);
            $comment->addColumn('updated', Type::INTEGER, ['unsigned' => true, 'length' => 11]);
            $comment->addColumn('description', Type::TEXT, ['notnull' => false]);
            $comment->setPrimaryKey(['id']);
            $comment->addIndex(['note_id']);
            $comment->addIndex(['user_id']);
            $comment->addIndex(['status']);
            $comment->addIndex(['created']);
            $comment->addIndex(['updated']);
        }
    }
}
