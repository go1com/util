<?php

namespace go1\util\flag;

use JsonSerializable;
use stdClass;

class FlagItem implements JsonSerializable
{
    public $id;
    public $entityType;
    public $entityId;
    public $level;
    public $context = [];
    public $original;

    private function __construct()
    {
        # Should not create the object directly.
    }

    public static function create(stdClass $input): FlagItem
    {
        $flagItem = new FlagItem;
        $flagItem->id = $input->id ?? null;
        $flagItem->entityType = $input->entity_type ?? null;
        $flagItem->entityId = $input->entity_id ?? null;
        $flagItem->level = $input->level ?? 0;

        return $flagItem;
    }

    public function diff(FlagItem $flagItem): array
    {
        $diff = [];

        if ($flagItem->entityId != $this->entityId) {
            $diff['description'] = $flagItem->entityId;
        }

        if ($flagItem->level != $this->level) {
            $diff['level'] = $flagItem->level;
        }

        return $diff;
    }

    public function jsonSerialize()
    {
        $array = [
            'id'            => (int)$this->id,
            'entity_type'   => $this->entityType,
            'entity_id'     => (int)$this->entityId,
            'level'         => (int)$this->level,
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