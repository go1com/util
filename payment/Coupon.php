<?php

namespace go1\util\payment;

use JsonSerializable;
use Ramsey\Uuid\Uuid;
use stdClass;

class Coupon implements JsonSerializable
{
    public $id;
    public $code;
    public $instanceId;
    public $entityType;
    public $entityId;
    public $userId;
    public $status;
    public $limitation;
    public $created;
    public $updated;
    public $original;

    private function __construct()
    {
        # Should not create the object directly.
    }

    public static function create(stdClass $input): Coupon
    {
        $coupon = new Coupon;
        $coupon->id = $input->id ?? null;
        $coupon->code = $input->code ?? Uuid::uuid4()->toString();
        $coupon->instanceId = $input->instance_id;
        $coupon->entityType = $input->entity_type ?? 'lo';
        $coupon->entityId = $input->entity_id ?? 0;
        $coupon->userId = $input->user_id ?? 0;
        $coupon->status = $input->status ?? 0;
        $coupon->limitation = $input->limitation ?? 1;
        $coupon->created = $input->created ?? time();
        $coupon->updated = $input->updated ?? time();

        return $coupon;
    }

    public function diff(Coupon $coupon): array
    {
        $diff = [];

        if ($coupon->entityType != $this->entityType) {
            $diff['entity_type'] = $coupon->entityType;
        }

        if ($coupon->entityId != $this->entityId) {
            $diff['entity_id'] = $coupon->entityId;
        }

        if ($coupon->status != $this->status) {
            $diff['status'] = $coupon->status;
        }

        if ($coupon->limitation != $this->limitation) {
            $diff['limitation'] = $coupon->limitation;
        }

        return $diff;
    }

    public function jsonSerialize()
    {
        $array = [
            'id'          => $this->id,
            'code'        => $this->code,
            'instance_id' => $this->instanceId,
            'entity_type' => $this->entityType,
            'entity_id'   => $this->entityId,
            'user_id'     => $this->userId,
            'status'      => $this->status,
            'limitation'  => $this->limitation,
            'created'     => $this->created,
            'updated'     => $this->updated,
        ];

        if ($this->original) {
            $array['original'] = $this->original;
        }

        return $array;
    }
}
