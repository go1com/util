<?php

namespace go1\util\eck\mock;

use Doctrine\DBAL\Connection;
use go1\util\DB;

trait EckMockTrait
{
    public function createField(Connection $db, $options = [])
    {
        static $autoId;

        $field = [
            'id'            => $options['id'] ?? ++$autoId,
            'instance'      => $options['instance'] ?? 1,
            'entity'        => $options['entity'] ?? 'user',
            'field'         => $options['field'] ?? 'field_first_name',
            'description'   => $options['description'] ?? 'bar',
            'label'         => $options['label'] ?? 'First Name',
            'help'          => $options['help'] ?? 'Help text',
            'type'          => $options['type'] ?? 'string',
            'required'      => $options['required'] ?? 0,
            'published'     => $options['published'] ?? 1,
            'weight'        => $options['weight'] ?? 0,
            'max_rows'      => $options['max_rows'] ?? 1,
            'parent_field'  => $options['parent_field'] ?? null,
            'data'          => $options['data'] ?? null,

        ];

        $db->insert('eck_structure', $field);
        return $db->lastInsertId('eck_structure');
    }

    public function createEntityValues(Connection $db, $instanceName, $entityType, $entityId, array $fields)
    {
        $fieldStructure = function($fieldName) use ($db, $instanceName, $entityType) {
            $sql = 'SELECT * FROM eck_structure WHERE field = ? AND instance = ? AND entity = ?';
            return $db
                ->executeQuery($sql, [$fieldName, $instanceName, $entityType])
                ->fetch(DB::OBJ);
        };

        foreach ($fields as $field => $values) {
            foreach ($values as $weight => $value) {
                if ($fieldInfo = $fieldStructure($field)) {
                    $db->insert("eck_value_$fieldInfo->type", ['value' => $value]);
                    $id = $db->lastInsertId("eck_value_{$field}");

                    $db->insert('eck_edge', [
                        'field_id'  => $fieldInfo->id,
                        'entity_id' => $entityId,
                        'value_id'  => $id,
                        'weight'    => $weight,
                    ]);
                }
            }
        }
    }
}
