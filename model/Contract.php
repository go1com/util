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
    private $instanceId;
    private $userId;
    private $name;
    private $status;
    private $startDate;
    private $signedDate;
    private $initialTerm;
    private $numberUsers;
    private $price;
    private $tax;
    private $taxIncluded;
    private $currency;
    private $frequency;
    private $frequencyOther;
    private $customTerm;
    private $paymentMethod;
    private $renewalDate;
    private $cancelDate;
    private $data;
    private $created;
    private $updated;

    public $userName;
    public $defaultTerms;

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
        $this->instanceId = $instanceId;
        $this->userId = $userId;
        $this->name = $name;
        $this->status = $status;
        $this->startDate = $startDate;
        $this->signedDate = $signedDate;
        $this->initialTerm = $initialTerm;
        $this->numberUsers = $numberOfUsers;
        $this->price = $price;
        $this->tax = $tax;
        $this->taxIncluded = $taxIncluded;
        $this->currency = $currency;
        $this->frequency = $frequency;
        $this->frequencyOther = $frequency_other;
        $this->customTerm = $customTerms;
        $this->paymentMethod = $paymentMethod;
        $this->renewalDate = $renewalDate;
        $this->cancelDate = $cancelDate;
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
        return $this->instanceId;
    }

    public function getUserId(): int
    {
        return $this->userId;
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
        return $this->startDate;
    }

    public function getSignedDate()
    {
        return $this->signedDate;
    }

    public function getInitialTerm()
    {
        return $this->initialTerm;
    }

    public function getNumberUsers()
    {
        return $this->numberUsers;
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
        return $this->taxIncluded;
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
        return $this->frequencyOther;
    }

    public function getCustomTerm(): string
    {
        return $this->customTerm;
    }

    public function getPaymentMethod()
    {
        return $this->paymentMethod;
    }

    public function getRenewalDate()
    {
        return $this->renewalDate;
    }

    public function getCancelDate()
    {
        return $this->cancelDate;
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
            $values['name'] = $this->name;
        }
        if ($origin->getStatus() != $this->status) {
            $values['status'] = $this->status;
        }
        if ($origin->getUserId() != $this->userId) {
            $values['user_id'] = $this->userId;
        }
        if ($origin->getStartDate() != $this->startDate) {
            $values['start_date'] = $this->startDate;
        }
        if ($origin->getSignedDate() != $this->signedDate) {
            $values['signed_date'] = $this->signedDate;
        }
        if ($origin->getInitialTerm() != $this->initialTerm) {
            $values['initial_term'] = $this->initialTerm;
        }
        if ($origin->getNumberUsers() != $this->numberUsers) {
            $values['number_users'] = $this->numberUsers;
        }
        if ($origin->getPrice() != $this->price) {
            $values['price'] = $this->price;
        }
        if ($origin->getTax() != $this->tax) {
            $values['tax'] = $this->tax;
        }
        if ($origin->getTaxIncluded() != $this->taxIncluded) {
            $values['tax_included'] = $this->taxIncluded;
        }
        if ($origin->getCurrency() != $this->currency) {
            $values['currency'] = $this->currency;
        }
        if ($origin->getFrequency() != $this->frequency) {
            $values['frequency'] = $this->frequency;
        }
        if ($origin->getFrequencyOther() != $this->frequencyOther) {
            $values['frequency_other'] = $this->frequencyOther;
        }
        if ($origin->getCustomTerm() != $this->customTerm) {
            $values['custom_term'] = $this->customTerm;
        }
        if ($origin->getPaymentMethod() != $this->paymentMethod) {
            $values['payment_method'] = $this->paymentMethod;
        }
        if ($origin->getRenewalDate() != $this->renewalDate) {
            $values['renewal_date'] = $this->renewalDate;
        }
        if ($origin->getCancelDate() != $this->cancelDate) {
            $values['cancel_date'] = $this->cancelDate;
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
            'instance_id'       => $this->instanceId,
            'user_id'           => $this->userId,
            'name'              => $this->name,
            'status'            => $this->getStatus(true),
            'start_date'        => $this->startDate,
            'signed_date'       => $this->signedDate,
            'initial_term'      => $this->initialTerm,
            'number_users'      => $this->numberUsers,
            'price'             => $this->price,
            'tax'               => $this->tax,
            'tax_included'      => $this->taxIncluded,
            'currency'          => $this->currency,
            'frequency'         => $this->frequency,
            'frequency_other'   => $this->frequencyOther,
            'custom_term'       => $this->customTerm,
            'payment_method'    => $this->paymentMethod,
            'renewal_date'      => $this->renewalDate,
            'cancel_date'       => $this->cancelDate,
            'data'              => $this->getData(true),
            'created'           => $this->created,
            'updated'           => $this->updated,
            'user_name'         => $this->userName,
            'default_terms'     => $this->defaultTerms
        ];
    }
}
