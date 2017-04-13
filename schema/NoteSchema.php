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
            $note->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
            $note->addColumn('entity_id', 'integer', ['unsigned' => true, 'type' => Type::getType(Type::BIGINT)]);
            $note->addColumn('profile_id', 'integer', ['unsigned' => true]);
            $note->addColumn('uuid', 'string', ['notnull' => false, 'length' => 36]);
            $note->addColumn('created', 'integer', ['unsigned' => true, 'length' => 11]);
            $note->addColumn('entity_type', 'string', ['notnull' => false, 'length' => 11, 'default' => 'lo']);
            $note->setPrimaryKey(['id']);
            $note->addUniqueIndex(['uuid']);
            $note->addIndex(['entity_id']);
            $note->addIndex(['profile_id']);
            $note->addIndex(['uuid']);
            $note->addIndex(['created']);
            $note->addIndex(['entity_type']);
        }
    }
}
