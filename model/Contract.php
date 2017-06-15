<?php

    namespace go1\util\model;

    use Assert\Assert;
    use Assert\Assertion;
    use go1\util\DateTime;
    use go1\util\Text;
    use JsonSerializable;
    use stdClass;

    class Contract implements JsonSerializable
    {
        const DEFAULT_CURRENCY  = 'AUD';
        const STATUS_ACTIVE     = 1;
        const STATUS_INACTIVE   = 0;
        const STATUS_CANCELED   = -1;

        public static $statuses = [-1, 0, 1];

        private $id;
        private $instanceId;
        private $userId;
        private $status;
        private $startDate;
        private $signedDate;
        private $initialTerm;
        private $numberOfUsers;
        private $price;
        private $tax;
        private $taxIncluded;
        private $currency;
        private $paymentMethod;
        private $renewalDate;
        private $cancelDate;
        private $data;
        private $created;
        private $updated;

        public function __construct(
            int $id = null,
            int $instanceId,
            int $userId,
            string $status = Contract::STATUS_ACTIVE,
            DateTime $startDate = null,
            DateTime $signedDate = null,
            string $initialTerm = null,
            int $numberOfUsers = null,
            float $price = null,
            float $tax = null,
            int $taxIncluded = null,
            string $currency = Contract::DEFAULT_CURRENCY,
            string $paymentMethod = null,
            DateTime $renewalDate = null,
            DateTime $cancelDate = null,
            stdClass $data = null,
            int $created = null,
            int $updated = null
        )
        {
            $this->id = $id;
            $this->instanceId = $instanceId;
            $this->userId = $userId;
            $this->status = $status;
            $this->startDate = $startDate;
            $this->signedDate = $signedDate;
            $this->initialTerm = $initialTerm;
            $this->numberOfUsers = $numberOfUsers;
            $this->price = $price;
            $this->tax = $tax;
            $this->taxIncluded = $taxIncluded;
            $this->currency = $currency;
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

        public function getStartDate(): string
        {
            return $this->startDate;
        }

        public function getSignedDate(): string
        {
            return $this->signedDate;
        }

        public function getInitialTerm(): string
        {
            return $this->initialTerm;
        }

        public function getNumberOfUsers(): int
        {
            return $this->numberOfUsers;
        }

        public function getPrice(): float
        {
            return $this->price;
        }

        public function getTax(): float
        {
            return $this->tax;
        }

        public function getTaxIncluded(): int
        {
            return $this->taxIncluded;
        }

        public function getCurrency(): string
        {
            return $this->currency ?? self::DEFAULT_CURRENCY;
        }

        public function getPaymentMethod(): string
        {
            return $this->paymentMethod;
        }

        public function getRenewalDate(): string
        {
            return $this->renewalDate ?? time();
        }

        public function getCancelDate(): string
        {
            return $this->cancelDate ?? time();
        }

        public function getData($stdClass = true)
        {
            if ($stdClass) {
                return is_scalar($this->data) ? json_decode($this->data) : $this->data;
            }

            return is_scalar($this->data) ? $this->data : json_encode($this->data);
        }

        public function getCreated(): int
        {
            return $this->created;
        }

        public function getUpdated(): int
        {
            return $this->updated;
        }

        public static function create(stdClass $row): Contract
        {
            Assert::lazy()
                ->that($row->status, 'contract.status')->inArray(Contract::$statuses)
                ->verifyNow();

            $row->start_date   = !empty($row->start_date) ? DateTime::create($row->start_date ? $row->start_date : time())->format(DATE_ISO8601) : null;
            $row->signed_date  = !empty($row->signed_date)? DateTime::create($row->signed_date ? $row->signed_date : time())->format(DATE_ISO8601) : null;
            $row->renewal_date = !empty($row->renewal_date) ? DateTime::create($row->renewal_date ? $row->renewal_date : time())->format(DATE_ISO8601) : null;
            $row->cancel_date  = !empty($row->cancel_date) ? DateTime::create($row->cancel_date ? $row->cancel_date : time())->format(DATE_ISO8601) : null;

            $row->price = number_format($row->price, 2);
            $row->tax = number_format($row->tax, 2);

            $row->data = !empty($row->data) ? (is_scalar($row->data) ? json_decode($row->data) : $row->data) : null;
            Text::purify(null, $row->data);

            $row->created = $row->created ?? time();
            $row->updated = $row->updated ?? time();

            return new Contract(
                $row->id,
                $row->instance_id,
                $row->user_id,
                $row->status,
                $row->start_date,
                $row->signed_date,
                $row->initial_term,
                $row->number_of_users,
                $row->price,
                $row->tax,
                $row->tax_included,
                $row->currency,
                $row->paymentMethod,
                $row->renewal_date,
                $row->cancel_date,
                $row->data,
                $row->created,
                $row->updated
            );
        }

        function jsonSerialize()
        {
            return [
                'id'             => $this->getId(),
                'instance_id'    => $this->getInstanceId(),
                'user_id'        => $this->getUserId(),
                'status'         => $this->getStatus(true),
                'start_date'     => $this->getStartDate(),
                'signed_date'    => $this->getSignedDate(),
                'initial_term'   => $this->getInitialTerm(),
                'number_users'   => $this->getNumberOfUsers(),
                'price'          => $this->getPrice(),
                'tax'            => $this->getTax(),
                'tax_included'   => $this->getTaxIncluded(),
                'currency'       => $this->getCurrency(),
                'payment_method' => $this->getPaymentMethod(),
                'renewal_date'   => $this->getRenewalDate(),
                'cancel_date'    => $this->getCancelDate(),
                'data'           => $this->getData(true),
                'created'        => $this->getCreated(),
                'updated'        => $this->getUpdated(),
            ];
        }
    }
