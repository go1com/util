<?php

namespace go1\util\collection;

use go1\util\Text;
use JsonSerializable;
use stdClass;

class Collection implements JsonSerializable
{
    const DEFAULT_MACHINE_NAME = 'default';

    public $id;
    public $type;
    public $machineName;
    public $title;
    public $status;
    public $portalId;
    public $authorId;
    public $data;
    public $timestamp;
    public $created;
    public $updated;
    /** @var Collection */
    public $original;

    public static function create(stdClass $input): Collection
    {
        Text::purify(null, $input);
        $collection = new Collection;
        $collection->id = $input->id ?? null;
        $collection->type = $input->type ?? null;
        $collection->machineName = $input->machine_name ?? null;
        $collection->title = $input->title ?? null;
        $collection->status = $input->status ?? CollectionStatus::ENABLED;
        $collection->portalId = $input->portal_id ?? null;
        $collection->authorId = $input->author_id ?? null;
        $data = $input->data ?? null;
        $collection->data = is_scalar($data) ? json_decode($data) : $data;
        $collection->timestamp = $input->timestamp ?? null;
        $collection->created = $input->created ?? null;
        $collection->updated = $input->updated ?? null;

        return $collection;
    }

    public function jsonSerialize()
    {
        $array = [
            'id'           => $this->id,
            'type'         => $this->type,
            'machine_name' => $this->machineName,
            'title'        => $this->title,
            'status'       => $this->status,
            'portal_id'    => $this->portalId,
            'author_id'    => $this->authorId,
            'data'         => json_encode($this->data),
            'timestamp'    => $this->timestamp,
            'created'      => $this->created,
            'updated'      => $this->updated,
        ];
        if ($this->original) {
            $array['original'] = $this->original->jsonSerialize();
        }

        return $array;
    }
}
