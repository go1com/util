<?php

namespace go1\util\es;

use JsonSerializable;
use stdClass;

class Document implements JsonSerializable
{
    public $type;
    public $id;
    public $portalId;
    public $parentId;
    public $body;

    public static function create(stdClass $input)
    {
        $document = new static();
        $document->type = $input->type;
        $document->id = $input->id;
        $document->portalId = $input->portalId;
        $document->parentId = $input->parentId ?? null;
        $document->body = $input->body ?? null;

        return $document;
    }

    public function jsonSerialize()
    {
        return [
            'type'     => $this->type,
            'id'       => $this->id,
            'portalId' => $this->portalId,
            'parentId' => $this->parentId,
            'body'     => $this->body,
        ];
    }
}
