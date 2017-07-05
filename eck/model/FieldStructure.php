<?php

namespace go1\util\eck\model;

use go1\util\DateTime;
use go1\util\eck\model\Permission;
use JsonSerializable;
use RuntimeException;
use stdClass;

class FieldStructure implements JsonSerializable
{
    private $id;
    private $name;
    private $instance;
    private $entity;
    private $description;
    private $label;
    private $help;
    private $type;
    private $mandatory;
    private $published;
    private $weight;
    private $maxRows;
    private $parentField;
    private $data;
    private $permissions = [];
    private $original;

    public function __construct($id, $name, $description, $label, $help, $type, $mandatory, $published, $weight, $maxRows, $parentField = null, $data = null, $instance = null, $entity = null)
    {
        $this->id = $id ? intval($id) : null;
        $this->name = $name;
        $this->description = $description;
        $this->label = $label;
        $this->help = $help;
        $this->type = $type;
        $this->mandatory = $mandatory;
        $this->published = $published;
        $this->weight = $weight;
        $this->maxRows = $maxRows;
        $this->parentField = $parentField;
        $this->data = $data;
        $this->instance = $instance;
        $this->entity = $entity;
    }

    /**
     * @param stdClass $row
     * @return FieldStructure
     */
    public static function create(stdClass $row)
    {
        return new static(
            $row->id,
            $row->field,
            $row->description,
            $row->label,
            $row->help,
            $row->type,
            $row->required,
            $row->published,
            $row->weight,
            $row->max_rows,
            $row->parent_field,
            $row->data,
            $row->instance,
            $row->entity
        );
    }

    public function setPermission(int $role, int $permission, bool $status)
    {
        $this->permissions[$permission][$role] = $status;
    }

    public function id()
    {
        return $this->id;
    }

    public function name()
    {
        return $this->name;
    }

    public function description()
    {
        return $this->description;
    }

    public function label()
    {
        return $this->label;
    }

    public function help()
    {
        return $this->help;
    }

    public function table()
    {
        switch ($this->type) {
            case 'string':
            case 'text':
            case 'integer':
            case 'float':
            case 'date':
            case 'datetime':
                return "eck_value_{$this->type}";

            default:
                throw new RuntimeException("Invalid data type: {$this->type}");
        }
    }

    /**
     * @return string[]
     */
    public function columns()
    {
        switch ($this->type) {
            case 'string':
            case 'text':
            case 'integer':
            case 'float':
            case 'date':
            case 'datetime':
                return ['value'];

            default:
                throw new RuntimeException("Invalid data type: {$this->type}");
        }
    }

    public function format(array $dbRow, bool $es = false)
    {
        unset($dbRow['id']);

        switch ($this->type) {
            case 'integer':
                $dbRow['value'] = intval($dbRow['value']);
                break;

            case 'float':
                $dbRow['value'] = floatval($dbRow['value']);
                break;
        }

        if ($es && $this->type === 'datetime') {
            $dbRow['value'] = DateTime::formatDate($dbRow['value']);
        }

        return $dbRow;
    }

    public function type()
    {
        return $this->type;
    }

    public function mandatory()
    {
        return $this->mandatory;
    }

    public function published()
    {
        return $this->published;
    }

    public function weight()
    {
        return $this->weight;
    }

    public function maxRows()
    {
        return $this->maxRows;
    }

    public function parentField()
    {
        return $this->parentField;
    }

    public function enum()
    {
        $enum = [];
        if ($this->data && is_scalar($this->data)) {
            $data = json_decode($this->data, true);

            $enum = isset($data['enum']) ? implode("\n", $data['enum']) : '';
        }

        return $enum;
    }

    /**
     * @return FieldStructure
     */
    public function original()
    {
        return $this->original;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function setLabel(string $label)
    {
        $this->label = $label;
    }

    public function setHelp(string $help)
    {
        $this->help = $help;
    }

    public function setParentField(string $parentField = null)
    {
        return $this->parentField = $parentField;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function setMandatory($mandatory)
    {
        $this->mandatory = $mandatory;
    }

    public function setPublished($published)
    {
        $this->published = $published;
    }

    public function setWeight($weight)
    {
        $this->weight = $weight;
    }

    public function setMaxRows($maxRows)
    {
        $this->maxRows = $maxRows;
    }

    public function setOriginal(FieldStructure $original)
    {
        $this->original = $original;
    }

    /**
     * @param FieldStructure $origin
     * @return mixed[]
     */
    public function getUpdatedValues(FieldStructure $origin)
    {
        if ($origin->description() != $this->description) {
            $values['description'] = $this->description;
        }

        if ($origin->label() != $this->label) {
            $values['label'] = $this->label;
        }

        if ($origin->help() != $this->help) {
            $values['help'] = $this->help;
        }

        if ($origin->parentField() != $this->parentField) {
            $values['parent_field'] = $this->parentField;
        }

        if ($origin->mandatory() != $this->mandatory) {
            $values['required'] = $this->mandatory;
        }

        if ($origin->published() != $this->published) {
            $values['published'] = $this->published;
        }

        if ($origin->weight() != $this->weight) {
            $values['weight'] = $this->weight;
        }

        if ($origin->maxRows() != $this->maxRows) {
            $values['max_rows'] = $this->maxRows;
        }

        return !empty($values) ? $values : [];
    }

    public function jsonSerialize()
    {
        foreach ($this->permissions as $permission => $roles) {
            foreach ($roles as $role => $status) {
                $permissions[Permission::permission($permission)][Permission::role($role)] = $status ? 'granted' : 'rejected';
            }
        }

        return [
            'id'           => $this->id,
            'name'         => $this->name,
            'instance'     => $this->instance,
            'entity'       => $this->entity,
            'description'  => $this->description,
            'label'        => $this->label,
            'help'         => $this->help,
            'type'         => $this->type,
            'mandatory'    => $this->mandatory,
            'published'    => $this->published,
            'weight'       => $this->weight,
            'max_rows'     => $this->maxRows,
            'parent_field' => $this->parentField,
            'enum'         => $this->enum(),
            'permission'   => !empty($permissions) ? $permissions : [],
            'original'     => $this->original,
        ];
    }

    public function instance()
    {
        return $this->instance;
    }

    public function entity()
    {
        return $this->entity;
    }
}
