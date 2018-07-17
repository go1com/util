<?php

namespace go1\util\enrolment;

use JsonSerializable;
use stdClass;

class ManualRecord implements JsonSerializable
{
    public $id;
    public $instanceId;
    public $entityType;
    public $entityId;
    public $userId;
    public $verified;
    public $data;
    public $created;
    public $updated;
    public $original;

    private function __construct()
    {
        // The object should not be created directly.
    }

    public static function create(stdClass $input): ManualRecord
    {
        $record = new ManualRecord;
        $record->id = $input->id ?? null;
        $record->instanceId = $input->instance_id;
        $record->entityType = $input->entity_type ?? null;
        $record->entityId = $input->entity_id ?? null;
        $record->userId = $input->user_id ?? null;
        $record->verified = isset($input->verified) ? boolval($input->verified) : false;
        $record->data = !isset($input->data) ? [] : (is_scalar($input->data) ? json_decode($input->data, true) : $input->data);
        $record->created = $input->created ?? time();
        $record->updated = $input->updated ?? time();

        return $record;
    }

    public function diff(ManualRecord $record): array
    {
        if ($this->entityType != $record->entityType) {
            $values['entity_type'] = $record->entityType;
        }

        if ($this->entityId != $record->entityId) {
            $values['entity_id'] = $record->entityId;
        }

        if ($this->verified != $record->verified) {
            $values['verified'] = $record->verified;
        }

        if (($this->data != $record->data)) {
            $values['data'] = is_scalar($record->data) ? $record->data : json_encode($record->data);
        }

        return $values ?? [];
    }

    public function jsonSerialize()
    {
        $return = [
            'id'          => $this->id,
            'instance_id' => $this->instanceId,
            'entity_type' => $this->entityType,
            'entity_id'   => $this->entityId,
            'user_id'     => $this->userId,
            'verified'    => $this->verified,
            'data'        => $this->data,
            'created'     => $this->created,
            'updated'     => $this->updated,
            'original'    => $this->original,
        ];

        if (isset($this->entity)) {
            $return['entity'] = $this->entity;
        }

        return $return;
    }
}
