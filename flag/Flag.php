<?php

namespace go1\util\flag;

use go1\util\text\Xss;
use JsonSerializable;
use stdClass;

class Flag implements JsonSerializable
{
    public $id;
    public $userId;
    public $flagId;
    public $entityType;
    public $entityId;
    public $description;
    public $reason;
    public $level;
    public $created;
    public $updated;
    public $context = [];
    public $original;

    private function __construct()
    {
        # Should not create the object directly.
    }

    public static function create(stdClass $input): Flag
    {
        $flag = new Flag;
        $flag->id = $input->id ?? null;
        $flag->userId = $input->user_id ?? 0;
        $flag->entityType = $input->entity_type ?? null;
        $flag->entityId = $input->entity_id ?? null;
        $flag->flagId = $input->flag_id ?? null;
        $flag->description = isset($input->description) ? Xss::filter($input->description) : null;
        $flag->reason = $input->reason?? null;
        $flag->level = $input->level ?? 0;
        $flag->created = $input->created ?? time();
        $flag->updated = $input->updated ?? time();

        return $flag;
    }

    public function diff(Flag $flag): array
    {
        $diff = [];

        if ($flag->description != $this->description) {
            $diff['description'] = $flag->description;
        }

        if ($flag->reason != $this->reason) {
            $diff['reason'] = $flag->reason;
        }

        if ($flag->level != $this->level) {
            $diff['level'] = $flag->level;
        }

        return $diff;
    }

    public function jsonSerialize()
    {
        $array = [
            'id'            => (int)$this->id,
            'user_id'       => (int)$this->userId,
            'entity_type'   => $this->entityType,
            'entity_id'     => (int)$this->entityId,
            'flag_id'       => (int)$this->flagId,
            'description'   => $this->description,
            'reason'        => (int)$this->reason,
            'level'         => (int)$this->level,
            'created'       => (int)$this->created,
            'updated'       => (int)$this->updated,
        ];

        if ($this->original) {
            $array['original'] = $this->original;
        }

        if ($this->context) {
            $array['context'] = $this->context;
        }

        return $array;
    }
}