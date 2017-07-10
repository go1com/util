<?php

namespace go1\util\schema\mock;

use Doctrine\DBAL\Connection;
use go1\util\Currency;
use go1\util\model\Contract;
use DateTime;

trait ContractMockTrait
{
    protected function createContract(Connection $db, array $options)
    {
        $data = isset($options['data']) ? (is_scalar($options['data']) ? json_decode($options['data'], true) : $options['data']) : [];
        $db->insert('contract', [
            'instance_id'       => $options['instance_id'] ?? 0,
            'user_id'           => $options['user_id'] ?? 0,
            'status'            => $options['status'] ?? Contract::STATUS_ACTIVE,
            'start_date'        => $options['start_date'] ?? (new DateTime)->format('Y-m-d'),
            'signed_date'       => $options['signed_date'] ?? (new DateTime)->format('Y-m-d'),
            'initial_term'      => $options['initial_term'] ?? '1 year',
            'number_users'      => $options['number_users'] ?? 1,
            'price'             => $options['price'] ?? 0,
            'tax'               => $options['tax'] ?? 0,
            'tax_included'      => $options['tax_included'] ?? '',
            'currency'          => $options['currency'] ?? Currency::DEFAULT,
            'payment_method'    => $options['payment_method'] ?? '',
            'renewal_date'      => $options['renewal_date'] ?? (new DateTime)->format('Y-m-d'),
            'cancel_date'       => $options['cancel_date'] ?? (new DateTime)->format('Y-m-d'),
            'data'              => json_encode($data),
            'created'           => $options['created'] ?? time(),
            'updated'           => $options['updated'] ?? time(),
        ]);

        return $db->lastInsertId('contract');
    }
}
