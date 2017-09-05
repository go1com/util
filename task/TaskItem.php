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

    private $id;
    private $taskId;
    private $created;
    private $data;
    private $name;
    private $status;

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

    public function getId(): int
    {
        return $this->id;
    }

    public function getTaskId(): int
    {
        return $this->taskId;
    }

    public function getData(): array
    {
        return is_scalar($this->data) ? json_decode($this->data, true) : $this->data;
    }

    public function getDataType(): string
    {
        $data = $this->getData();

        return $data['type'];
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function setStatus(int $status)
    {
        $this->status = $status;
    }

    public static function create(stdClass $row): TaskItem
    {
        return new TaskItem(
            $row->id ?? null,
            $row->task_id,
            $row->created,
            $row->data,
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
            'data'        => $this->getData(),
            'name'        => $this->name,
            'status'      => $this->status
        ];
    }
}
