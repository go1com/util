<?php

namespace go1\util\activity;

use JsonSerializable;
use stdClass;

class Activity implements JsonSerializable
{
    public $id;
    public $instanceId;
    public $actorId;
    public $userId;
    public $actionId;
    public $entityType;
    public $entityId;
    public $created;
    public $updated;
    public $data;
    public $context;

    private function __construct()
    {
        # The object should not be created manually.
    }

    public static function create(stdClass $row)
    {
        $row->data = $row->data ?? [];
        $activity = new static;
        $activity->id = $row->id ?? null;
        $activity->instanceId = $row->instance_id ?? null;
        $activity->actorId = $row->action_id ?? null;
        $activity->userId = $row->user_id ?? null;
        $activity->actionId = $row->action_id ?? null;
        $activity->entityType = $row->entity_type ?? null;
        $activity->entityId = $row->entity_id ?? null;
        $activity->created = $row->created ?? time();
        $activity->updated = $row->updated ?? $activity->created;
        $activity->data = is_null($row->data) ? [] : (is_string($row->data) ? json_decode($row->data) : (object) []);
        $activity->context = $row->data->context ?? [];

        return $activity;
    }

    /**
     * @param mixed $instanceId
     * @return Activity
     */
    public function setInstanceId($instanceId)
    {
        $this->instanceId = $instanceId;

        return $this;
    }

    /**
     * @param mixed $actorId
     * @return Activity
     */
    public function setActorId($actorId)
    {
        $this->actorId = $actorId;

        return $this;
    }

    /**
     * @param mixed $userId
     * @return Activity
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * @param mixed $actionId
     * @return Activity
     */
    public function setActionId($actionId)
    {
        $this->actionId = $actionId;

        return $this;
    }

    /**
     * @param mixed $entityType
     * @return Activity
     */
    public function setEntityType($entityType)
    {
        $this->entityType = $entityType;

        return $this;
    }

    /**
     * @param mixed $entityId
     * @return Activity
     */
    public function setEntityId($entityId)
    {
        $this->entityId = $entityId;

        return $this;
    }

    /**
     * @param mixed $created
     * @return Activity
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * @param mixed $updated
     * @return Activity
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * @param mixed $data
     * @return Activity
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @param mixed $context
     * @return Activity
     */
    public function setContext($context)
    {
        $this->context = $context;

        return $this;
    }

    public function diff(Activity $activity)
    {
        $values = [];

        if ($this->updated != $activity->updated) {
            $values['updated'] = $activity->updated;
        }

        if ($this->data != $activity->data) {
            $values['data'] = $activity->data;
        }

        return $values;
    }

    function jsonSerialize()
    {
        $this->data->context = $this->context;
        return [
            'id'          => $this->id,
            'instance_id' => $this->instanceId,
            'actor_id'    => $this->actorId,
            'user_id'     => $this->userId,
            'action_id'   => $this->actionId,
            'entity_type' => $this->entityType,
            'entity_id'   => $this->entityId,
            'created'     => $this->created,
            'updated'     => $this->updated,
            'data'        => json_encode($this->data),
        ];
    }
}
