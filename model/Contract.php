<?php

namespace go1\util\model;

use Assert\Assertion;
use go1\util\Currency;
use go1\util\DateTime;
use go1\util\Text;
use JsonSerializable;
use stdClass;

class Contract implements JsonSerializable
{
    const STATUS_ACTIVE     = 1;
    const STATUS_INACTIVE   = 0;
    const STATUS_CANCELED   = -1;

    public static $statuses = [-1, 0, 1];

    private $id;
    private $instance_id;
    private $user_id;
    private $name;
    private $status;
    private $start_date;
    private $signed_date;
    private $initial_term;
    private $number_users;
    private $price;
    private $tax;
    private $tax_included;
    private $currency;
    private $frequency;
    private $frequency_other;
    private $custom_term;
    private $payment_method;
    private $renewal_date;
    private $cancel_date;
    private $data;
    private $created;
    private $updated;

    public function __construct(
        int $id = null,
        int $instanceId,
        int $userId,
        string $name = null,
        string $status = Contract::STATUS_ACTIVE,
        string $startDate = null,
        string $signedDate = null,
        string $initialTerm = null,
        int $numberOfUsers = null,
        float $price = null,
        float $tax = null,
        string $taxIncluded = null,
        string $currency = Currency::DEFAULT,
        string $frequency = null,
        string $frequency_other = null,
        string $customTerms = null,
        string $paymentMethod = null,
        string $renewalDate = null,
        string $cancelDate = null,
        $data = null,
        int $created = null,
        int $updated = null
    )
    {
        $this->id = $id;
        $this->instance_id = $instanceId;
        $this->user_id = $userId;
        $this->name = $name;
        $this->status = $status;
        $this->start_date = $startDate;
        $this->signed_date = $signedDate;
        $this->initial_term = $initialTerm;
        $this->number_users = $numberOfUsers;
        $this->price = $price;
        $this->tax = $tax;
        $this->tax_included = $taxIncluded;
        $this->currency = $currency;
        $this->frequency = $frequency;
        $this->frequency_other = $frequency_other;
        $this->custom_term = $customTerms;
        $this->payment_method = $paymentMethod;
        $this->renewal_date = $renewalDate;
        $this->cancel_date = $cancelDate;
        $this->data = $data;
        $this->created = $created;
        $this->updated = $updated;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getInstanceId(): int
    {
        return $this->instance_id;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getStatus(bool $string = false)
    {
        if ($string) {
            switch ($this->status) {
                case static::STATUS_CANCELED:
                    return 'canceled';

                case static::STATUS_INACTIVE:
                    return 'inactive';

                case static::STATUS_ACTIVE:
                    return 'active';
            }
        }

        return (int) $this->status;
    }

    public function setStatus(int $status)
    {
        Assertion::inArray($status, static::$statuses);

        $this->status = $status;

        return $this;
    }

    public function isValid(): bool
    {
        return self::STATUS_ACTIVE == $this->status;
    }

    public function getStartDate()
    {
        return $this->start_date;
    }

    public function getSignedDate()
    {
        return $this->signed_date;
    }

    public function getInitialTerm()
    {
        return $this->initial_term;
    }

    public function getNumberUsers()
    {
        return $this->number_users;
    }

    public function getPrice()
    {
        return $this->price;
    }

    public function getTax()
    {
        return $this->tax;
    }

    public function getTaxIncluded()
    {
        return $this->tax_included;
    }

    public function getCurrency()
    {
        return $this->currency ?? Currency::DEFAULT;
    }

    public function getFrequency(): string
    {
        return $this->frequency;
    }

    public function getFrequencyOther(): string
    {
        return $this->frequency_other;
    }

    public function getCustomTerm(): string
    {
        return $this->custom_term;
    }

    public function getPaymentMethod()
    {
        return $this->payment_method;
    }

    public function getRenewalDate()
    {
        return $this->renewal_date;
    }

    public function getCancelDate()
    {
        return $this->cancel_date;
    }

    public function getData($stdClass = false)
    {
        if ($stdClass) {
            return is_scalar($this->data) ? json_decode($this->data) : $this->data;
        }

        return is_scalar($this->data) ? $this->data : json_encode($this->data);
    }

    public function getCreated(): int
    {
        return $this->created ?? time();
    }

    public function getUpdated(): int
    {
        return $this->updated ?? time();
    }

    public function set($propertyName, $propertyValue)
    {
        $this->{$propertyName} = $propertyValue;
    }

    public function threadName(): string
    {
        return 'contract:' . $this->id;
    }

    public static function format(stdClass &$row)
    {
        $row->status = (int) $row->status;
        $row->name = $row->name ?? '';

        $row->start_date   = !empty($row->start_date) ? DateTime::create($row->start_date ? $row->start_date : time())->format(DATE_ISO8601) : null;
        $row->signed_date  = !empty($row->signed_date)? DateTime::create($row->signed_date)->format(DATE_ISO8601) : null;
        $row->renewal_date = !empty($row->renewal_date) ? DateTime::create($row->renewal_date)->format(DATE_ISO8601) : null;
        $row->cancel_date  = !empty($row->cancel_date) ? DateTime::create($row->cancel_date)->format(DATE_ISO8601) : null;

        $row->price = number_format($row->price, 2, '.', '');
        $row->tax = number_format($row->tax, 2, '.', '');
        $row->tax_included = $row->tax_included ?? '';
        $row->currency = !empty($row->currency) ? strtoupper($row->currency) : null;
        $row->frequency = $row->frequency ?? '';
        $row->frequency_other = $row->frequency_other ?? '';
        $row->custom_term = $row->custom_term ?? '';
        $row->payment_method = $row->payment_method ?? '';

        $row->data = !empty($row->data) ? (is_scalar($row->data) ? json_decode($row->data) : $row->data) : null;
        Text::purify(null, $row->data);

        $row->created = $row->created ?? time();
        $row->updated = $row->updated ?? time();
    }

    public static function create(stdClass $row): Contract
    {
        self::format($row);

        return new Contract(
            $row->id ?? null,
            $row->instance_id,
            $row->user_id,
            $row->name,
            $row->status,
            $row->start_date,
            $row->signed_date,
            $row->initial_term,
            $row->number_users,
            $row->price,
            $row->tax,
            $row->tax_included,
            $row->currency,
            $row->frequency,
            $row->frequency_other,
            $row->custom_term,
            $row->payment_method,
            $row->renewal_date,
            $row->cancel_date,
            $row->data,
            $row->created,
            $row->updated
        );
    }

    public function getUpdatedValues(Contract $origin): array
    {
        if ($origin->getName() != $this->name) {
            $values['status'] = $this->name;
        }
        if ($origin->getStatus() != $this->status) {
            $values['status'] = $this->status;
        }
        if ($origin->getUserId() != $this->user_id) {
            $values['user_id'] = $this->user_id;
        }
        if ($origin->getStartDate() != $this->start_date) {
            $values['start_date'] = $this->start_date;
        }
        if ($origin->getSignedDate() != $this->signed_date) {
            $values['signed_date'] = $this->signed_date;
        }
        if ($origin->getInitialTerm() != $this->initial_term) {
            $values['initial_term'] = $this->initial_term;
        }
        if ($origin->getNumberUsers() != $this->number_users) {
            $values['number_users'] = $this->number_users;
        }
        if ($origin->getPrice() != $this->price) {
            $values['price'] = $this->price;
        }
        if ($origin->getTax() != $this->tax) {
            $values['tax'] = $this->tax;
        }
        if ($origin->getTaxIncluded() != $this->tax_included) {
            $values['tax_included'] = $this->tax_included;
        }
        if ($origin->getCurrency() != $this->currency) {
            $values['currency'] = $this->currency;
        }
        if ($origin->getFrequency() != $this->frequency) {
            $values['frequency'] = $this->frequency;
        }
        if ($origin->getFrequencyOther() != $this->frequency_other) {
            $values['frequency_other'] = $this->frequency_other;
        }
        if ($origin->getCustomTerm() != $this->custom_term) {
            $values['custom_term'] = $this->custom_term;
        }
        if ($origin->getPaymentMethod() != $this->payment_method) {
            $values['payment_method'] = $this->payment_method;
        }
        if ($origin->getRenewalDate() != $this->renewal_date) {
            $values['renewal_date'] = $this->renewal_date;
        }
        if ($origin->getCancelDate() != $this->cancel_date) {
            $values['cancel_date'] = $this->cancel_date;
        }

        if (empty($values)) {
            return [];
        }

        $values['updated'] = time();

        return $values;
    }

    function jsonSerialize()
    {
        return [
            'id'                => $this->id,
            'instance_id'       => $this->instance_id,
            'user_id'           => $this->user_id,
            'name'              => $this->name,
            'status'            => $this->getStatus(true),
            'start_date'        => $this->start_date,
            'signed_date'       => $this->signed_date,
            'initial_term'      => $this->initial_term,
            'number_users'      => $this->number_users,
            'price'             => $this->price,
            'tax'               => $this->tax,
            'tax_included'      => $this->tax_included,
            'currency'          => $this->currency,
            'frequency'         => $this->frequency,
            'frequency_other'   => $this->frequency_other,
            'custom_term'       => $this->custom_term,
            'payment_method'    => $this->payment_method,
            'renewal_date'      => $this->renewal_date,
            'cancel_date'       => $this->cancel_date,
            'data'              => $this->getData(true),
            'created'           => $this->created,
            'updated'           => $this->updated,
        ];
    }
}
