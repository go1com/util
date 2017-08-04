<?php

namespace go1\util\payment;

use Assert\Assert;
use DateTime;
use go1\util\text\Xss;
use JsonSerializable;
use Ramsey\Uuid\Uuid;
use stdClass;

class Coupon implements JsonSerializable
{
    const TYPE_PERCENT = 1;
    const TYPE_VALUE   = 2;

    const STATUS_UNAVAILABLE = -1; # No longer available/Reach limitation.
    const STATUS_UNPUBLISHED = 0; # Author does want publish the coupon yet.
    const STATUS_AVAILABLE   = 1; # Available for usage.
    const STATUS_ALL         = [-1, 0, 1];

    public $id;
    public $instanceId;
    public $entities = [];
    public $userId;
    public $title;
    public $code;
    public $type;
    public $value;
    public $limitation;
    public $limitationPerUser;
    /** @var  DateTime */
    public $expiration;
    public $status;
    public $created;
    public $updated;
    public $context = [];
    public $original;

    private function __construct()
    {
        # Should not create the object directly.
    }

    public function type(): string
    {
        return (self::TYPE_PERCENT == $this->type) ? '%' : '$';
    }

    public static function create(stdClass $input): Coupon
    {
        $coupon = new Coupon;
        $coupon->id = $input->id ?? null;
        $coupon->instanceId = $input->instance_id;
        $coupon->userId = $input->user_id ?? 0;
        $coupon->title = isset($input->title) ? Xss::filter($input->title) : null;
        $coupon->code = $input->code ?? Uuid::uuid4()->toString();
        $coupon->type = $input->type ?? self::TYPE_VALUE;
        $coupon->value = $input->value ?? 0.00;
        $coupon->status = $input->status ?? 0;
        $coupon->limitation = $input->limitation ?? 1;
        $coupon->limitationPerUser = $input->limitation_per_user ?? 0;
        $coupon->created = $input->created ?? time();
        $coupon->updated = $input->updated ?? time();
        $coupon->expiration = !isset($input->expiration) ? null : $input->expiration;

        if ($coupon->expiration) {
            if (is_numeric($coupon->expiration)) {
                $coupon->expiration = new DateTime();
                $coupon->expiration->setTimestamp($input->expiration);
            }
            elseif (is_string($coupon->expiration)) {
                $coupon->expiration = new DateTime($coupon->expiration);
            }
        }

        if (!empty($input->entities)) {
            foreach ($input->entities as $entityType => $entityIds) {
                foreach ($entityIds as $entityId) {
                    $coupon->add($entityType, $entityId);
                }
            }
        }

        return $coupon;
    }

    public function add(string $entityType, int $entityId)
    {
        $this->entities[$entityType][] = $entityId;
        $this->entities[$entityType] = array_unique($this->entities[$entityType]);
    }

    public function remove(string $entityType, int $entityId)
    {
        foreach ($this->entities as $type => $ids) {
            if ($entityType == $type) {
                foreach ($ids as $i => $id) {
                    if ($entityId == $id) {
                        unset($this->entities[$type][$i]);
                    }
                }
            }
        }
    }

    public function validateCartItems(array $items)
    {
        $assert = Assert::lazy();
        foreach ($items as $i => &$item) {
            $assert->that($item['instanceId'] ?? null, "item_{$i}.instanceId")->eq($this->instanceId);
        }

        $assert->verifyNow();
    }

    public function diff(Coupon $coupon): array
    {
        $diff = [];

        if ($coupon->title != $this->title) {
            $diff['title'] = $coupon->title;
        }

        if ($coupon->code != $this->code) {
            $diff['code'] = $coupon->code;
        }

        if ($coupon->entities != $this->entities) {
            $diff['entities'] = $coupon->entities;
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
            $diff['expiration'] = is_scalar($coupon->expiration) ? $coupon->expiration : $coupon->expiration->format(DateTime::ISO8601);
        }

        if ($coupon->limitation != $this->limitation) {
            $diff['limitation'] = $coupon->limitation;
        }

        if ($coupon->limitationPerUser != $this->limitationPerUser) {
            $diff['limitation_per_user'] = $coupon->limitationPerUser;
        }

        return $diff;
    }

    public function expired(): bool
    {
        if ($this->expiration) {
            return $this->expiration < new DateTime;
        }

        return false;
    }

    public function jsonSerialize()
    {
        $array = [
            'id'                  => $this->id,
            'instance_id'         => $this->instanceId,
            'entities'            => $this->entities,
            'user_id'             => $this->userId,
            'title'               => $this->title,
            'code'                => $this->code,
            'type'                => $this->type,
            'value'               => $this->value,
            'status'              => $this->status,
            'expiration'          => ($this->expiration instanceof DateTime) ? $this->expiration->format(DATE_ISO8601) : $this->expiration,
            'limitation'          => $this->limitation,
            'limitation_per_user' => $this->limitationPerUser,
            'created'             => $this->created,
            'updated'             => $this->updated,
        ];

        if ($this->original) {
            $array['original'] = $this->original;
        }

        if ($this->context) {
            $array['context'] = $this->context;
        }

        return $array;
    }
}
