<?php

namespace go1\util\collection;

use go1\util\Text;
use JsonSerializable;
use stdClass;

class CollectionGroupSelectionItem implements JsonSerializable
{
    public $id;
    public $groupId;
    public $collectionId;
    public $custom;
    public $timestamp;

    public static function create(stdClass $input): CollectionGroup
    {
        Text::purify(null, $input);
        $group = new CollectionGroupSelectionItem();
        $group->id = $input->id ?? null;
        $group->groupId = $input->group_id ?? null;
        $group->collectionId = $input->collection_id ?? null;
        $group->custom = $input->custom ?? 0;
        $group->timestamp = $input->timestamp ?? null;

        return $group;
    }

    public function jsonSerialize()
    {
        $array = [
            'id'            => $this->id,
            'group_id'      => $this->groupId,
            'collection_id' => $this->collectionId,
            'custom'        => $this->custom,
            'timestamp'     => $this->timestamp,
        ];

        return $array;
    }
}
