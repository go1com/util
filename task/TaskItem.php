<?php

namespace go1\util\task;

use JsonSerializable;
use stdClass;

class TaskItem implements JsonSerializable
{
    const STATUS_PENDING        = 0;
    const STATUS_PROCESSING     = 1;
    const STATUS_COMPLETED      = 2;
    const STATUS_FAILED         = 3;

    public $id;
    public $taskId;
    public $created;
    public $data;
    public $name;
    public $status;

    public function __construct(
        int $id = null,
        int $taskId,
        int $created = null,
        $data = null,
        string $name = '',
        int $status = self::STATUS_PENDING
    )
    {
        $this->id = $id;
        $this->taskId = $taskId;
        $this->created = $created;
        $this->data = $data;
        $this->name = $name;
        $this->status = $status;
    }

    public function getDataType(): string
    {
        return $this->data['type'];
    }

    public static function create(stdClass $row): TaskItem
    {
        $data = is_scalar($row->data) ? json_decode($row->data, true) : $row->data;

        return new TaskItem(
            $row->id ?? null,
            $row->task_id,
            $row->created,
            $data,
            $row->name,
            $row->status
        );
    }

    function jsonSerialize()
    {
        return [
            'id'          => $this->id,
            'task_id'     => $this->taskId,
            'created'     => $this->created,
            'data'        => $this->data,
            'name'        => $this->name,
            'status'      => $this->status
        ];
    }
}
