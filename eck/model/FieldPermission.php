<?php

namespace go1\util\eck\model;

class FieldPermission
{
    private $fieldId;
    private $role;
    private $permission;
    private $status;

    public function __construct(int $fieldId, int $role, int $permission, bool $status)
    {
        $this->fieldId = $fieldId;
        $this->role = $role;
        $this->permission = $permission;
        $this->status = $status;
    }

    public function role(): int
    {
        return $this->role;
    }

    public function permission(): int
    {
        return $this->permission;
    }

    public function status(): bool
    {
        return $this->status;
    }
}
