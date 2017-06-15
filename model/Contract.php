<?php

namespace go1\util\model;

use stdClass;

class Contract
{
    public $id;
    public $instanceId;
    public $userId;
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
        $contract->id = $row->id;
        $contract->instanceId = $row->instance_id;
        $contract->userId = $row->user_id;
        $contract->startDate = $row->start_date;
        $contract->signedDate = $row->signed_date;
        $contract->initialTerm = $row->initial_term;
        $contract->numberUsers = $row->number_of_users;
        $contract->price = $row->price;
        $contract->tax = $row->tax;
        $contract->taxIncluded = $row->tax_included;
        $contract->currency = $row->currency;
        $contract->paymentMethod = $row->payment_method;
        $contract->cancelDate = $row->cancel_date;
        $contract->data = $row->data;
        $contract->created = $row->created;
        $contract->updated = $row->updated;

        return $contract;
    }
}
