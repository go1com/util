<?php

namespace go1\util\model;

use JsonSerializable;
use stdClass;

class Edge implements JsonSerializable
{
    public $id;
    public $type;
    public $sourceId;
    public $targetId;
    public $weight;
    public $data;

    /** @var Edge */
    public $original;

    public static function create(stdClass $row): Edge
    {
        $edge = new Edge;
        $edge->id = $row->id;
        $edge->type = $row->type;
        $edge->sourceId = $row->source_id;
        $edge->targetId = $row->target_id;
        $edge->weight = $row->weight;
        $edge->data = is_string($row->data) ? json_decode($row->data) : (object) $row->data;

        return $edge;
    }

    public function jsonSerialize()
    {
        return [
            'id'        => $this->id,
            'type'      => $this->type,
            'source_id' => $this->sourceId,
            'target_id' => $this->targetId,
            'weight'    => $this->weight,
            'data'      => json_decode(json_encode($this->data), true),
            'original'  => $this->original ? $this->original->jsonSerialize() : null,
        ];
    }
}
