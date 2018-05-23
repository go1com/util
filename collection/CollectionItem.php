<?php

namespace go1\util\collection;

use go1\util\Text;
use JsonSerializable;
use stdClass;

class CollectionItem implements JsonSerializable
{
    public $id;
    public $collectionId;
    public $loId;
    public $timestamp;

    public static function create(stdClass $input): CollectionItem
    {
        Text::purify(null, $input);
        $item = new CollectionItem();
        $item->id = $input->id ?? null;
        $item->collectionId = $input->collection_id ?? null;
        $item->loId = $input->lo_id ?? null;
        $item->timestamp = $input->timestamp ?? null;

        return $item;
    }

    public function jsonSerialize()
    {
        $array = [
            'id'            => $this->id,
            'collection_id' => $this->collectionId,
            'lo_id'         => $this->loId,
            'timestamp'     => $this->timestamp,
        ];

        return $array;
    }
}

