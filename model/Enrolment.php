<?php

namespace go1\util\model;

use stdClass;

class Enrolment
{
    public $id;
    public $profileId;
    public $parentLoId;
    public $loId;
    public $instanceId;
    public $takenInstanceId;
    public $startDate;
    public $endDate;
    public $status;
    public $result;
    public $pass;
    public $changed;
    public $revisions = [];

    public static function create(stdClass $row): Enrolment
    {
        $enrolment = new Enrolment;
        $enrolment->id = $row->id;
        $enrolment->profileId = $row->profile_id;
        $enrolment->parentLoId = $row->parent_lo_id;
        $enrolment->loId = $row->lo_id;
        $enrolment->instanceId = $row->instance_id;
        $enrolment->takenInstanceId = $row->taken_instance_id;
        $enrolment->startDate = $row->start_date ?? null;
        $enrolment->endDate = $row->end_date ?? null;
        $enrolment->status = $row->status;
        $enrolment->result = $row->result;
        $enrolment->pass = $row->pass;
        $enrolment->changed = $row->changed;
        $enrolment->timestamp = $row->timestamp;

        return $enrolment;
    }
}
