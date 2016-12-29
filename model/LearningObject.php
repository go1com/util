<?php

namespace go1\util\model;

use Doctrine\DBAL\Connection;
use stdClass;

/**
 * Just for reference, not ready for using yet.
 */
class LearningObject
{
    public $id;
    public $type;
    public $language;
    public $title;
    public $description;
    public $instanceId;
    public $remoteId;
    public $originId;
    public $private;
    public $published;
    public $marketplace;
    public $event;
    public $tags;
    public $image;
    public $created;
    public $updated;
    public $timestamp;
    public $data;
    public $edges;

    public static function create(stdClass $row, int $userProfileId = null, Connection $db = null)
    {
        $lo = new LearningObject;
        $lo->id = $row->id;
        $lo->type = $row->type;
        $lo->language = $row->language;
        $lo->title = $row->title;
        $lo->description = $row->description;
        $lo->instanceId = $row->instance_id;
        $lo->remoteId = $row->remote_id;
        $lo->originId = $row->origin_id;
        $lo->private = $row->private;
        $lo->published = $row->published;
        $lo->marketplace = $row->marketplace;
        $lo->event = $row->event;
        $lo->tags = $row->tags;
        $lo->image = $row->image;
        $lo->created = $row->created;
        $lo->updated = $row->updated;
        $lo->timestamp = $row->timestamp;
        $lo->data = is_string($row->data) ? json_decode($row->data) : $row->data;

        # …
        # $lo->edges = $row->edges;

        return $lo;
    }
}
