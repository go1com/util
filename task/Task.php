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

    private $id;
    private $instanceId;
    private $userId;
    private $created;
    private $data;
    private $name;
    private $status;
    private $updated;

    public function __construct(
        int $id = null,
        int $instanceId,
        int $userId,
        int $created = null,
        $data = null,
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

    public function getId(): int
    {
        return $this->id;
    }

    public function getInstanceId(): int
    {
        return $this->instanceId;
    }

    public function getUserId(): int
    {
        return $this->userId;
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

    public static function create(stdClass $row): Task
    {
        return new Task(
            $row->id ?? null,
            $row->instance_id,
            $row->user_id,
            $row->created,
            $row->data,
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
            'data'        => $this->getData(),
            'name'        => $this->name,
            'status'      => $this->status,
            'updated'     => $this->updated,
        ];
    }
}
