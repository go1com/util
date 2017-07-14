<?php

namespace go1\util\payment;


use Doctrine\DBAL\Connection;
use go1\util\DB;

class PaymentHelper
{

    public static function loadTransaction(Connection $db, int $paymentId)
    {
        return $db
            ->executeQuery('SELECT * FROM payment_transaction WHERE id = ?', [$paymentId])
            ->fetch(DB::OBJ);
    }
}
