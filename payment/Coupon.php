<?php

namespace go1\util\payment;

use Assert\Assert;
use JsonSerializable;
use Ramsey\Uuid\Uuid;
use stdClass;

class Coupon implements JsonSerializable
{
    const TYPE_PERCENT = 1;
    const TYPE_VALUE   = 2;

    public $id;
    public $instanceId;
    public $entityType;
    public $entityId;
    public $userId;
    public $code;
    public $type;
    public $value;
    public $limitation;
    public $expiration;
    public $status;
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
        $coupon->instanceId = $input->instance_id;
        $coupon->entityType = $input->entity_type ?? 'lo';
        $coupon->entityId = $input->entity_id ?? 0;
        $coupon->userId = $input->user_id ?? 0;
        $coupon->code = $input->code ?? Uuid::uuid4()->toString();
        $coupon->type = $input->type ?? self::TYPE_VALUE;
        $coupon->value = $input->value ?? 0.00;
        $coupon->status = $input->status ?? 0;
        $coupon->limitation = $input->limitation ?? 1;
        $coupon->created = $input->created ?? time();
        $coupon->updated = $input->updated ?? time();

        return $coupon;
    }

    public function validateCartItems(array $items)
    {
        $assert = Assert::lazy();
        foreach ($items as $i => &$item) {
            $assert->that($item->instanceId ?? null, "item_{$i}")->eq($this->instanceId);
        }

        $assert->verifyNow();
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

        if ($coupon->type != $this->type) {
            $diff['type'] = $coupon->type;
        }

        if ($coupon->value != $this->value) {
            $diff['value'] = $coupon->value;
        }

        if ($coupon->status != $this->status) {
            $diff['status'] = $coupon->status;
        }

        if ($coupon->expiration != $this->expiration) {
            $diff['expiration'] = $coupon->expiration;
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
            'instance_id' => $this->instanceId,
            'entity_type' => $this->entityType,
            'entity_id'   => $this->entityId,
            'user_id'     => $this->userId,
            'code'        => $this->code,
            'type'        => $this->type,
            'value'       => $this->value,
            'status'      => $this->status,
            'expiration'  => $this->expiration,
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
