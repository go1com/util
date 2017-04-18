<?php

namespace go1\util\enrolment;

use JsonSerializable;
use stdClass;

class ManualRecord implements JsonSerializable
{
    public $id;
    public $entityType;
    public $entityId;
    public $userId;
    public $verified;
    public $data;
    public $created;
    public $updated;

    private function __construct()
    {
        // The object should not be created directly.
    }

    public static function create(stdClass $input): ManualRecord
    {
        $record = new ManualRecord;
        $record->id = $input->id ?? null;
        $record->entityType = $input->entity_type ?? null;
        $record->entityId = $input->entity_id ?? null;
        $record->userId = $input->user_id ?? null;
        $record->verified = $input->verified ?? false;
        $record->data = $input->data ?? (object) [];
        $record->created = $input->created ?? time();
        $record->updated = $input->updated ?? time();
    }

    public function diff(ManualRecord $record): array
    {
        ($this->verified != $record->verified) && $values['verified'] = $record->verified;
        ($this->data != $record->data) && $values['data'] = $record->data;

        return $values ?? [];
    }

    public function jsonSerialize()
    {
        return [
            'entity_type' => $this->entityType,
            'entity_id'   => $this->entityId,
            'user_id'     => $this->userId,
            'verified'    => $this->verified,
            'data'        => $this->data,
            'created'     => $this->created,
            'updated'     => $this->updated,
        ];
    }
}
