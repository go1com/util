<?php

namespace go1\util\model;

use JsonSerializable;
use stdClass;

class Enrolment implements JsonSerializable
{
    public $id;
    public $profileId;
    public $parentEnrolmentId;
    public $loId;
    public $instanceId;
    public $takenPortalId;
    public $startDate;
    public $endDate;
    public $dueDate;
    public $status;
    public $result;
    public $pass;
    public $changed;
    public $timestamp;
    public $data;
    public $revisions = [];

    /**
     * @var Enrolment
     */
    public $original;

    /**
     * @deprecated
     */
    public $parentLoId;

    public static function create(stdClass $row = null): Enrolment
    {
        $enrolment = new Enrolment;
        $enrolment->id = $row->id ?? null;
        $enrolment->profileId = $row->profile_id ?? null;
        $enrolment->parentLoId = $row->parent_lo_id ?? 0;
        $enrolment->parentEnrolmentId = $row->parent_enrolment_id ?? 0;
        $enrolment->loId = $row->lo_id ?? null;
        $enrolment->instanceId = $row->instance_id ?? 0;
        $enrolment->takenPortalId = $row->taken_instance_id ?? null;;
        $enrolment->startDate = $row->start_date ?? null;
        $enrolment->endDate = $row->end_date ?? null;
        $enrolment->dueDate = $row->due_date ?? null;
        $enrolment->status = $row->status ?? null;
        $enrolment->result = $row->result ?? 0;
        $enrolment->pass = $row->pass ?? 0;
        $enrolment->changed = $row->changed ?? null;
        $enrolment->timestamp = $row->timestamp ?? null;

        $data = $row->data ?? null;
        $enrolment->data = is_scalar($data) ? json_decode($data) : $data;

        return $enrolment;
    }

    public function jsonSerialize()
    {
        $array = [
            'id'                  => $this->id,
            'profile_id'          => $this->profileId,
            'parent_lo_id'        => $this->parentLoId,
            'parent_enrolment_id' => $this->parentEnrolmentId,
            'lo_id'               => $this->loId,
            'instance_id'         => $this->instanceId,
            'taken_instance_id'   => $this->takenPortalId,
            'start_date'          => $this->startDate,
            'end_date'            => $this->endDate,
            'status'              => $this->status,
            'result'              => $this->result,
            'pass'                => $this->pass,
            'timestamp'           => $this->timestamp,
            'changed'             => $this->changed,
            'data'                => $this->data,
        ];

        if ($this->original) {
            $array['original'] = $this->original->jsonSerialize();
        }

        return $array;
    }
}
