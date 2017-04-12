<?php

namespace go1\util\eck\model;

use JsonSerializable;

class EntityMetadata implements JsonSerializable
{
    private $instance;
    private $entityType;

    /** @var  FieldStructure[] */
    private $fields = [];

    public function __construct($instance, $entityType, array $fields)
    {
        $this->instance = $instance;
        $this->entityType = $entityType;

        foreach ($fields as $field) {
            $this->addField($field);
        }
    }

    /**
     * @return string
     */
    public function instance(): string
    {
        return $this->instance;
    }

    /**
     * @return string
     */
    public function entityType(): string
    {
        return $this->entityType;
    }

    /**
     * @return FieldStructure[]
     */
    public function &fields()
    {
        return $this->fields;
    }

    public function hasFields(): bool
    {
        return !empty($this->fields);
    }

    public function hasField(string $name): bool
    {
        return isset($this->fields[$name]);
    }

    public function addField(FieldStructure &$field)
    {
        $this->fields[$field->name()] = $field;
    }

    /**
     * @param $fieldName
     * @return false|FieldStructure
     */
    public function field(string $fieldName)
    {
        return isset($this->fields[$fieldName]) ? $this->fields[$fieldName] : false;
    }

    /**
     * @return int[]
     */
    public function fieldIds()
    {
        foreach ($this->fields as $field) {
            $ids[] = $field->id();
        }

        return !empty($ids) ? $ids : [];
    }

    public function jsonSerialize()
    {
        return [
            'instance'    => $this->instance,
            'entity_type' => $this->entityType,
            'fields'      => $this->fields,
        ];
    }
}
