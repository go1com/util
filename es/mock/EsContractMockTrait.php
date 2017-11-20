<?php

namespace go1\util\es\mock;

use Elasticsearch\Client;
use go1\util\Currency;
use go1\util\DateTime;
use go1\util\es\Schema;
use go1\util\model\Contract;

trait EsContractMockTrait
{
    public function createEsContract(Client $client, $options = [])
    {
        static $id;

        $options['data'] = isset($options['data'])
            ? (is_scalar($options['data']) ? json_decode($options['data'], true) : json_decode(json_encode($options['data']), true))
            : [];

        $contract = [
            'id'                => $options['id'] ?? ++$id,
            'instance_id'       => $options['instance_id'] ?? 0,
            'user_id'           => $options['user_id'] ?? 0,
            'staff_id'          => $options['staff_id'] ?? 0,
            'parent_id'         => $options['parent_id'] ?? 0,
            'csm_id'            => $options['csm_id'] ?? 0,
            'name'              => $options['name'] ?? '',
            'status'            => $options['status'] ?? Contract::STATUS_ACTIVE,
            'start_date'        => $options['start_date'] ?? null,
            'signed_date'       => $options['signed_date'] ?? null,
            'initial_term'      => $options['initial_term'] ?? '1 year',
            'number_users'      => $options['number_users'] ?? 1,
            'price'             => $options['price'] ?? 0,
            'tax'               => $options['tax'] ?? 0,
            'tax_included'      => $options['tax_included'] ?? '',
            'currency'          => $options['currency'] ?? Currency::DEFAULT,
            'aud_net_amount'    => $options['aud_net_amount'] ?? 0,
            'frequency'         => $options['frequency'] ?? '',
            'frequency_other'   => $options['frequency_other'] ?? '',
            'custom_term'       => $options['custom_term'] ?? '',
            'payment_method'    => $options['payment_method'] ?? '',
            'renewal_date'      => $options['renewal_date'] ?? null,
            'cancel_date'       => $options['cancel_date'] ?? null,
            'created'           => $options['created'] ?? time(),
            'updated'           => $options['updated'] ?? time(),
        ];

        $client->create([
            'index'   => $options['index'] ?? Schema::INDEX,
            'type'    => Schema::O_CONTRACT,
            'id'      => $options['_id'] ?? $contract['id'],
            'body'    => $contract,
            'refresh' => true
        ]);

        return $contract['id'];
    }
}
