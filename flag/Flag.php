<?php

namespace go1\util\flag;

use go1\util\flag\FlagReason;
use go1\util\text\Xss;
use JsonSerializable;
use stdClass;

class Flag implements JsonSerializable
{
    const LEVEL_NONE     = 0;
    const LEVEL_TRIVIAL  = 1;
    const LEVEL_LOW      = 2;
    const LEVEL_MEDIUM   = 3;
    const LEVEL_HIGH     = 4;
    const LEVEL_CRITICAL = 5;

    const FLAG_LEVELS = [FLAG::LEVEL_NONE, FLAG::LEVEL_TRIVIAL, FLAG::LEVEL_LOW, FLAG::LEVEL_MEDIUM, FLAG::LEVEL_HIGH, FLAG::LEVEL_CRITICAL];

    public $id;
    public $instanceId;
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
        $flag->instanceId = $input->instance_id;
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
            'instance_id'   => (int)$this->instanceId,
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