<?php

namespace go1\util\schema\mock;

use Doctrine\DBAL\Connection;
use go1\credit\domain\model\Credit;
use Ramsey\Uuid\Uuid;

trait CreditMockTrait
{
    protected function createCredit(Connection $db, array $options)
    {
        $db->insert('credit', [
            'owner_id'       => isset($options['owner_id']) ? $options['owner_id'] : -1,
            'portal_id'      => isset($options['portal_id']) ? $options['portal_id'] : -1,
            'product_type'   => isset($options['product_type']) ? $options['product_type'] : 'lo',
            'product_id'     => isset($options['product_id']) ? $options['product_id'] : -1,
            'transaction_id' => isset($options['transaction_id']) ? $options['transaction_id'] : null,
            'created'        => isset($options['created']) ? $options['created'] : time(),
            'updated'        => isset($options['updated']) ? $options['updated'] : time(),
            'status'         => isset($options['status']) ? $options['status'] : Credit::STATUS_DISABLED,
            'token'          => isset($options['token']) ? $options['token'] : Uuid::uuid4()->toString(),
            'privacy'        => isset($options['privacy']) ? $options['privacy'] : Credit::PRIVACY_HIDDEN,
        ]);

        return $db->lastInsertId('credit');
    }
}
