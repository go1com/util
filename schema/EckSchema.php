<?php

namespace go1\util\schema;

use Doctrine\DBAL\Schema\Schema;

class EckSchema
{
    public static function install(Schema $schema)
    {
        if (!$schema->hasTable('eck_structure')) {
            $permission = $schema->createTable('eck_permission');
            $permission->addColumn('instance', 'string');
            $permission->addColumn('entity', 'string');
            $permission->addColumn('role', 'string');
            $permission->addColumn('permission', 'smallint', ['unsigned' => true]);
            $permission->addColumn('status', 'boolean');
            $permission->setPrimaryKey(['instance', 'entity', 'role', 'permission']);
            $permission->addIndex(['status']);

            $fieldPermission = $schema->createTable('eck_permission_field');
            $fieldPermission->addColumn('field_id', 'integer', ['unsigned' => true]);
            $fieldPermission->addColumn('role', 'string');
            $fieldPermission->addColumn('permission', 'smallint', ['unsigned' => true]);
            $fieldPermission->addColumn('status', 'boolean');
            $fieldPermission->setPrimaryKey(['field_id', 'role', 'permission']);
            $fieldPermission->addForeignKeyConstraint('eck_structure', ['field_id'], ['id']);
            $fieldPermission->addIndex(['status']);

            $structure = $schema->createTable('eck_structure');
            $structure->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
            $structure->addColumn('instance', 'string');
            $structure->addColumn('entity', 'string');
            $structure->addColumn('field', 'string');
            $structure->addColumn('description', 'string');
            $structure->addColumn('label', 'string', ['notnull' => false]);
            $structure->addColumn('help', 'string', ['notnull' => false]);
            $structure->addColumn('type', 'string');
            $structure->addColumn('required', 'string');
            $structure->addColumn('published', 'boolean', ['default' => true]);
            $structure->addColumn('weight', 'integer');
            $structure->addColumn('max_rows', 'string'); # TODO: should be integer.
            $structure->addColumn('parent_field', 'string', ['notnull' => false]);
            $structure->addColumn('data', 'blob', ['notnull' => false]);

            $structure->setPrimaryKey(['id']);
            $structure->addUniqueIndex(['instance', 'entity', 'field']);
            $structure->addIndex(['instance']);
            $structure->addIndex(['published']);
            $structure->addIndex(['weight']);
        }

        if (!$schema->hasTable('eck_value_string')) {
            // Type: STRING - Should be used for short text only, string length <= 255.
            $valueString = $schema->createTable('eck_value_string');
            $valueString->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
            $valueString->addColumn('value', 'string');
            $valueString->setPrimaryKey(['id']);
            $valueString->addIndex(['value']);

            // Type: TEXT - No index, can be used for long text.
            $valueText = $schema->createTable('eck_value_text');
            $valueText->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
            $valueText->addColumn('value', 'blob');
            $valueText->setPrimaryKey(['id']);

            // Type: Integer
            $valueInteger = $schema->createTable('eck_value_integer');
            $valueInteger->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
            $valueInteger->addColumn('value', 'integer');
            $valueInteger->setPrimaryKey(['id']);
            $valueInteger->addIndex(['value']);

            // Type: Float
            $valueFloat = $schema->createTable('eck_value_float');
            $valueFloat->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
            $valueFloat->addColumn('value', 'float');
            $valueFloat->setPrimaryKey(['id']);
            $valueFloat->addIndex(['value']);

            // Type: Date
            $valueDate = $schema->createTable('eck_value_date');
            $valueDate->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
            $valueDate->addColumn('value', 'date');
            $valueDate->setPrimaryKey(['id']);
            $valueDate->addIndex(['value']);

            // Type: DateTime
            $valueDateTime = $schema->createTable('eck_value_datetime');
            $valueDateTime->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
            $valueDateTime->addColumn('value', 'datetime');
            $valueDateTime->setPrimaryKey(['id']);
            $valueDateTime->addIndex(['value']);
        }

        if (!$schema->hasTable('eck_edge')) {
            $edge = $schema->createTable('eck_edge');
            $edge->addColumn('field_id', 'integer', ['unsigned' => true]);
            $edge->addColumn('entity_id', 'integer', ['unsigned' => true]);
            $edge->addColumn('value_id', 'integer', ['unsigned' => true]);
            $edge->addColumn('weight', 'integer');
            $edge->addUniqueIndex(['field_id', 'entity_id', 'value_id']);
            $edge->addIndex(['weight']);
        }
    }
}
