<?php

namespace go1\util\activity;

use JsonSerializable;
use stdClass;

class Activity implements JsonSerializable
{
    public $id;
    public $instanceId;
    public $actorId;
    public $actionType;
    public $entityType;
    public $entityId;
    public $created;
    public $updated;
    public $data;

    private function __construct()
    {
        # The object should not be created manually.
    }

    public static function create(stdClass $row)
    {
        $activity = new static;
        $activity->id = $row->id ?? null;
        $activity->instanceId = $row->instance_id;
        $activity->actorId = $row->actor_id ?? null;
        $activity->actionType = $row->action_type;
        $activity->entityType = $row->entity_type;
        $activity->entityId = $row->entity_id;
        $activity->created = $row->created ?? time();
        $activity->updated = $row->updated ?? $activity->created;
        $activity->data = is_null($row->data) ? null : (is_string($row->data) ? json_decode($row->data) : (object) []);

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
        return [
            'id'          => $this->id,
            'instance_id' => $this->instanceId,
            'actor_id'    => $this->actorId,
            'action_type' => $this->actionType,
            'entity_type' => $this->entityType,
            'entity_id'   => $this->entityId,
            'created'     => $this->created,
            'updated'     => $this->updated,
            'data'        => $this->data,
        ];
    }
}
