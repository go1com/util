<?php

namespace go1\util\flag;

use Assert\Assert;
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

    const REASON_LOADING       = 1;
    const REASON_GRAMMAR       = 2;
    const REASON_INACCURACY    = 3;
    const REASON_INAPPROPRIATE = 4;

    const FLAG_LEVELS = [FLAG::LEVEL_NONE, FLAG::LEVEL_TRIVIAL, FLAG::LEVEL_LOW, FLAG::LEVEL_MEDIUM, FLAG::LEVEL_HIGH, FLAG::LEVEL_CRITICAL];
    const FLAG_REASONS = [FLAG::REASON_LOADING, FLAG::REASON_GRAMMAR, FLAG::REASON_INACCURACY, FLAG::REASON_INAPPROPRIATE];

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
        $flag->entityType = $input->entityType;
        $flag->entityId = $input->entityId;
        $flag->description = isset($input->description) ? Xss::filter($input->description) : null;
        $flag->reason = isset($input->reason) ? Xss::filter($input->reason) : null;
        $flag->level = $input->level ?? 0;
        $flag->created = $input->created ?? time();
        $flag->updated = $input->updated ?? time();

        return $flag;
    }

    public function jsonSerialize()
    {
        $array = [
            'id'            => $this->id,
            'instance_id'   => $this->instanceId,
            'user_id'       => $this->userId,
            'description'   => $this->description,
            'reason'        => $this->reason,
            'level'         => $this->level,
            'created'       => $this->created,
            'updated'       => $this->updated,
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