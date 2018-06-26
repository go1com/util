<?php

namespace go1\util\policy;

use go1\util\Text;
use JsonSerializable;
use stdClass;

class PolicyItem implements JsonSerializable
{
    public $id;
    public $type;
    public $portalId;
    public $hostEntityType;
    public $hostEntityId;
    public $entityType;
    public $entityId;
    public $created;
    public $updated;
    public $original;

    public static function create(stdClass $input): PolicyItem
    {
        Text::purify(null, $input);

        $item = new PolicyItem;
        $item->id = $input->id ?? Text::uniqueId();
        $item->type = $input->type ?? Realm::VIEW;
        $item->portalId = $input->portal_id ?? null;
        $item->hostEntityType = $input->host_entity_type ?? null;
        $item->hostEntityId = $input->host_entity_id ?? null;
        $item->entityType = $input->entity_type ?? null;
        $item->entityId = $input->entity_id ?? null;
        $item->created = $input->created ?? null;
        $item->updated = $input->updated ?? null;

        return $item;
    }

    public function jsonSerialize()
    {
        return [
            'id'               => $this->id,
            'type'             => $this->type,
            'portal_id'        => $this->portalId,
            'host_entity_type' => $this->hostEntityType,
            'host_entity_id'   => $this->hostEntityId,
            'entity_type'      => $this->entityType,
            'entity_id'        => $this->entityId,
            'created'          => $this->created,
            'updated'          => $this->updated,
        ];
    }
}
