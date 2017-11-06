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
    public $tags;

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
        $activity->actorId = $row->actor_id ?? null;
        $activity->userId = $row->user_id ?? null;
        $activity->actionId = $row->action_id ?? null;
        $activity->entityType = $row->entity_type ?? null;
        $activity->entityId = $row->entity_id ?? null;
        $activity->created = $row->created ?? time();
        $activity->updated = $row->updated ?? $activity->created;
        $activity->data = is_null($row->data) ? [] : (is_string($row->data) ? json_decode($row->data) : (object) []);
        $activity->context = $activity->data->context ?? [];
        $activity->tags = $activity->data->tags ?? [];

        return $activity;
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
        $this->data->tags = $this->tags;
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
