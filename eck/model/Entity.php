<?php

namespace go1\util\eck\model;

use JsonSerializable;

class Entity implements JsonSerializable
{
    private $instance;
    private $entityType;
    private $id;
    private $fields = [];

    public function __construct(string $instance, string $entityType, $id, array $fields = [])
    {
        $this->instance = $instance;
        $this->entityType = $entityType;
        $this->id = $id;

        foreach ($fields as $name => $value) {
            $this->set($name, $value);
        }
    }

    public function instance(): string
    {
        return $this->instance;
    }

    public function entityType(): string
    {
        return $this->entityType;
    }

    public function id()
    {
        return $this->id;
    }

    public function fields(): array
    {
        return $this->fields;
    }

    public function set($name, array $value = null, $delta = null)
    {
        (null === $delta)
            ? ($this->fields[$name] = $value)
            : ($this->fields[$name][$delta] = $value);
    }

    public function get($name, $delta = null)
    {
        return (null === $delta)
            ? (isset($this->fields[$name]) ? $this->fields[$name] : false)
            : (isset($this->fields[$name][$delta]) ? $this->fields[$name][$delta] : false);
    }

    public function jsonSerialize()
    {
        return [
                'instance'    => $this->instance,
                'entity_type' => $this->entityType,
                'id'          => $this->id,
            ] + $this->fields;
    }
}
