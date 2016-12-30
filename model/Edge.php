<?php

namespace go1\util\model;

use stdClass;

class Edge
{
    public $id;
    public $sourceId;
    public $targetId;
    public $weight;
    public $data;

    public static function create(stdClass $row): Edge
    {
        $edge = new Edge;
        $edge->id = $row->id;
        $edge->sourceId = $row->source_id;
        $edge->targetId = $row->target_id;
        $edge->weight = $row->weight;
        $edge->data = is_string($row->data) ? json_decode($row->data) : $row->data;

        return $edge;
    }
}
