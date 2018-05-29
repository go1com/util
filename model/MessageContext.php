<?php

namespace go1\util\model;

use JsonSerializable;
use stdClass;

class MessageContext implements JsonSerializable
{
    public $actorId;
    public $action;
    public $timestamp;
    public $description;
    public $requestId;
    public $internal;
    public $portalName;
    public $entityType;

    /**
     * @deprecated
     */
    public $instance;

    public static function create(stdClass $input = null)
    {
        $context = new MessageContext();
        if (is_null($input)) {
            return $context;
        }

        $context->actorId = $row->actor_id ?? null;
        $context->timestamp = $row->timestamp ?? null;
        $context->action = $row->action ?? null;
        $context->requestId = $row->request_id ?? null;
        $context->internal = $row->internal ?? null;
        $context->portalName = $row->portal_name ?? null;
        $context->instance = $row->instance ?? null;
        $context->entityType = $row->entity_type ?? null;

        return $context;
    }

    function jsonSerialize()
    {
        $this->instance = $this->instance ?: $this->portalName;

        return [
            'actor_id'    => $this->actorId,
            'action'      => $this->actorId,
            'timestamp'   => $this->timestamp,
            'request_id'  => $this->requestId,
            'instance'    => $this->instance,
            'portal-name' => $this->portalName,
            'entity_type' => $this->entityType,
        ];
    }
}
