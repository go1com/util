<?php

namespace go1\util\plan;

use DateTime as DefaultDateTime;
use go1\util\DateTime;
use go1\util\Text;
use JsonSerializable;
use stdClass;

class Plan implements JsonSerializable
{
    const STATUS_INTERESTING = -3; # Learner interest in the object, but no action provided yet.
    const STATUS_ASSIGNED    = -2; # Learner self-assigned, or by someone.
    const STATUS_ENQUIRED    = -1; # Learner interesting in the object, enquired.
    const STATUS_PENDING     = 0; # The object is not yet available.
    const STATUS_IN_PROGRESS = 1; # Learning in progress.
    const STATUS_COMPLETED   = 2; # The plan is completed.
    const STATUS_FAILED      = 4; # The plan is completed, but result is not good.
    const STATUS_LATE        = 4; # Learning was assigned & was not able to complete the plan ontime.
    const STATUS_EXPIRED     = 3; # The object is expired.

    const TYPE_AWARD         = 'award';

    /** @var integer */
    public $id;

    /** @var integer */
    public $userId;

    /** @var integer */
    public $assignerId;

    /** @var  string */
    public $entityType;

    /** @var  integer */
    public $entityId;

    /** @var  integer */
    public $status;

    /** @var  DefaultDateTime */
    public $created;

    /** @var  DefaultDateTime */
    public $due;

    /** @var object */
    public $data;

    /** @var  Plan */
    public $original;

    private function __construct()
    {
        // The object should not be created directly.
    }

    public static function create(stdClass $input): Plan
    {
        $plan = new Plan;
        $plan->id = isset($input->id) ? $input->id : null;
        $plan->userId = isset($input->user_id) ? $input->user_id : null;
        $plan->assignerId = isset($input->assigner_id) ? $input->assigner_id : null;
        $plan->entityType = $input->entity_type;
        $plan->entityId = $input->entity_id;
        $plan->status = $input->status;
        $plan->created = DateTime::create($input->created_date ? $input->created_date : time());
        $plan->due = $input->due_date ? DateTime::create($input->due_date) : null;
        $plan->data = !$input->data ? null : (is_scalar($input->data) ? json_decode($input->data) : $input->data);
        Text::purify(null, $plan->data);

        return $plan;
    }

    public function diff(Plan $plan)
    {
        $values = [];

        ($this->status != $plan->status) && $values['status'] = $plan->status;
        ($this->due != $plan->due) && $values['due_date'] = $plan->due ? $plan->due->format(DATE_ISO8601) : null;
        ($this->data != $plan->data) && $values['data'] = is_scalar($plan->data) ? $plan->data : json_encode($plan->data);

        return $values;
    }

    function jsonSerialize()
    {
        return [
            'id'           => $this->id,
            'user_id'      => $this->userId,
            'assigner_id'  => $this->assignerId,
            'entity_type'  => $this->entityType,
            'entity_id'    => $this->entityId,
            'status'       => $this->status,
            'created_date' => $this->created->format(DATE_ISO8601),
            'due_date'     => $this->due ? $this->due->format(DATE_ISO8601) : null,
            'data'         => $this->data,
            'original'     => $this->original,
        ];
    }
}
