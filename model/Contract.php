<?php

namespace go1\util\model;

use go1\util\DateTime;
use go1\util\Text;
use stdClass;

class Contract
{
    const DEFAULT_CURRENCY  = 'AUD';
    const STATUS_ACTIVE     = 1;
    const STATUS_INACTIVE   = 0;
    const STATUS_CANCELED   = -1;

    public $id;
    public $instanceId;
    public $userId;
    public $status;
    public $startDate;
    public $signedDate;
    public $initialTerm;
    public $numberUsers;
    public $price;
    public $tax;
    public $taxIncluded;
    public $currency;
    public $paymentMethod;
    public $cancelDate;
    public $data;
    public $created;
    public $updated;

    public static function create(stdClass $row): Contract
    {
        $contract = new Contract;
        $contract->id               = $row->id ?? null;
        $contract->instanceId       = $row->instance_id;
        $contract->userId           = $row->user_id;
        $contract->status           = $row->status ?? self::STATUS_ACTIVE;
        $contract->startDate        = DateTime::create($row->start_date ? $row->start_date : time())->format(DATE_ISO8601);
        $contract->signedDate       = $row->signed_date ?? null;
        $contract->initialTerm      = $row->initial_term ?? null;
        $contract->numberUsers      = $row->number_of_users ?? null;
        $contract->price            = $row->price ?? null;
        $contract->tax              = $row->tax ?? null;
        $contract->taxIncluded      = $row->tax_included ?? false;
        $contract->currency         = $row->currency ?? self::DEFAULT_CURRENCY;
        $contract->paymentMethod    = $row->payment_method ?? null;
        $contract->cancelDate       = !empty($row->cancel_date) ? DateTime::create($row->cancel_date)->format(DATE_ISO8601) : null;
        $contract->data             = !empty($row->data) ? (is_scalar($row->data) ? json_decode($row->data) : $row->data) : null;
        Text::purify(null, $contract->data);
        $contract->created          = $row->created ?? time();
        $contract->updated          = $row->updated ?? time();

        return $contract;
    }
}
