<?php

namespace go1\util\task;

use JsonSerializable;
use stdClass;

class Task implements JsonSerializable
{
    const STATUS_PENDING        = 0;
    const STATUS_PROCESSING     = 1;
    const STATUS_COMPLETED      = 2;
    const STATUS_FAILED         = 3;

    public $id;
    public $instanceId;
    public $userId;
    public $created;
    public $data;
    public $name;
    public $status;
    public $updated;

    public function __construct(
        int $id = null,
        int $instanceId,
        int $userId,
        int $created = null,
        array $data = [],
        string $name = '',
        int $status = self::STATUS_PENDING,
        int $updated = null
    )
    {
        $this->id = $id;
        $this->instanceId = $instanceId;
        $this->userId = $userId;
        $this->created = $created;
        $this->data = $data;
        $this->name = $name;
        $this->status = $status;
        $this->updated = $updated;
    }

    public function getDataType(): string
    {
        return $this->data['type'];
    }

    public static function create(stdClass $row): Task
    {
        $data = is_scalar($row->data) ? json_decode($row->data, true) : $row->data;

        return new Task(
            $row->id ?? null,
            $row->instance_id,
            $row->user_id,
            $row->created,
            $data,
            $row->name,
            $row->status,
            $row->updated
        );
    }

    function jsonSerialize()
    {
        return [
            'id'          => $this->id,
            'instance_id' => $this->instanceId,
            'user_id'     => $this->userId,
            'created'     => $this->created,
            'data'        => $this->data,
            'name'        => $this->name,
            'status'      => $this->status,
            'updated'     => $this->updated,
        ];
    }
}
