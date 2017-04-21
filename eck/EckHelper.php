<?php

namespace go1\util\eck;

use Doctrine\DBAL\Connection;
use go1\util\DB;
use go1\util\eck\model\Entity;
use go1\util\eck\model\EntityMetadata;
use go1\util\eck\model\FieldStructure;

class EckHelper
{
    public static function metadata(Connection $db, $instance, $entityType, $filter = true): EntityMetadata
    {
        $metadata = new EntityMetadata($instance, $entityType, []);

        $sql = 'SELECT * FROM eck_structure WHERE instance = ? AND entity = ?';
        $sql .= $filter ? ' AND published = 1' : '';
        $q = $db->executeQuery($sql, [$instance, $entityType]);
        while ($row = $q->fetch(DB::OBJ)) {
            $structure = FieldStructure::create($row);

            // Attach permissions
            $qq = $db->executeQuery('SELECT * FROM eck_permission_field WHERE field_id = ?', [$structure->id()]);
            while ($realm = $qq->fetch(DB::OBJ)) {
                $structure->setPermission($realm->role, $realm->permission, $realm->status ? true : false);
            }

            $metadata->addField($structure);
        }

        return $metadata;
    }


    public static function load(Connection $db, $instance, string $entityType, int $entityId): Entity
    {
        $entity = new Entity($instance, $entityType, $entityId);
        $metadata = static::metadata($db, $instance, $entityType);

        $edges = $db->executeQuery(
            'SELECT * FROM eck_edge WHERE field_id IN (?) AND entity_id = ?',
            [$metadata->fieldIds(), $entityId],
            [DB::INTEGERS, DB::INTEGER]
        );

        $valueIds = $valueWeights = $valueToFields = [];
        while ($edge = $edges->fetch(DB::OBJ)) {
            foreach ($metadata->fields() as $field) {
                if ($edge->field_id == $field->id()) {
                    $table = $field->table();
                    $valueToFields[$table][$edge->value_id] = $field->name();
                    $valueIds[$table][] = $edge->value_id;
                    $valueWeights[$table][$edge->value_id] = $edge->weight;
                }
            }
        }

        if (!empty($valueIds)) {
            foreach ($valueIds as $table => $ids) {
                $q = $db->executeQuery("SELECT * FROM {$table} WHERE id in (?)", [$ids], [Connection::PARAM_INT_ARRAY]);
                while ($value = $q->fetch()) {
                    $id = $value['id'];
                    foreach ($metadata->fields() as $field) {
                        if ($id == $field->id()) {
                            $field->format($value);
                        }
                    }

                    $name = $valueToFields[$table][$id];
                    $weight = $valueWeights[$table][$id];
                    $entity->set($name, $value, $weight);
                }
            }
        }

        return $entity;
    }

    public static function field(Connection $db, int $fieldId)
    {
        $row = $db->executeQuery('SELECT * FROM eck_structure WHERE id = ?', [$fieldId])->fetch(DB::OBJ);
        return $row ? FieldStructure::create($row) : false;
    }
}
