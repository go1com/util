<?php

namespace go1\util\payment\mock;

use Doctrine\DBAL\Connection;
use go1\util\payment\Coupon;
use Ramsey\Uuid\Uuid;

trait PaymentMockTrait
{
    public function createTransaction(Connection $db, array $options = [])
    {
        $db->insert('payment_transaction', [
            'email'            => isset($options['email']) ? $options['email'] : 'test-payment@domain.com',
            'status'           => isset($options['status']) ? $options['status'] : 0,
            'amount'           => isset($options['amount']) ? $options['amount'] : 0,
            'currency'         => isset($options['currency']) ? $options['currency'] : 'USD',
            'data'             => isset($options['data']) ? $options['data'] : '',
            'created'          => isset($options['created']) ? $options['created'] : time(),
            'updated'          => isset($options['updated']) ? $options['updated'] : time(),
            'payment_method'   => isset($options['payment_method']) ? $options['payment_method'] : 'stripe',
        ]);

        return $db->lastInsertId('payment_transaction');
    }

    public function createTransactionItem(Connection $db, array $options = [])
    {
        $db->insert('payment_transaction_items', [
            'transaction_id'    => isset($options['transaction_id']) ? $options['transaction_id'] : 1,
            'product_type'      => isset($options['product_type']) ? $options['product_type'] : 'product',
            'product_id'        => isset($options['product_id']) ? $options['product_id'] : 1,
            'qty'               => isset($options['qty']) ? $options['qty'] : 1,
            'price'             => isset($options['price']) ? $options['price'] : 0.0,
            'tax'               => isset($options['tax']) ? $options['tax'] : 0.00,
            'tax_included'      => isset($options['tax_included']) ? $options['tax_included'] : true,
            'data'              => isset($options['data']) ? $options['data'] : '',
        ]);

        return $db->lastInsertId('payment_transaction_items');
    }

    public function createCoupon(Connection $db, array $options = [])
    {
        $db->insert('payment_coupon', [
            'title'             => $options['title'] ?? '',
            'code'              => $options['code'] ?? Uuid::uuid4()->toString(),
            'instance_id'       => $options['instance_id'] ?? 0,
            'user_id'           => $options['user_id'] ?? 1,
            'type'              => $options['type'] ?? Coupon::TYPE_PERCENT,
            'value'             => $options['value'] ?? 10,
            'status'            => $options['status'] ?? 1,
            'limitation'        => $options['limitation'] ?? 1,
            'expiration'        => $options['expiration'] ?? (new \DateTime('+1 week'))->format(DATE_ISO8601),
            'created'           => $options['created'] ?? time(),
            'updated'           => $options['updated'] ?? time(),
        ]);

        return $db->lastInsertId('payment_coupon');
    }

    public function createCouponItem(Connection $db, array $options = [])
    {
        $db->insert('payment_coupon_item', [
            'coupon_id'         => $options['coupon_id'] ?? 1,
            'entity_type'       => $options['entity_type'] ?? 'lo',
            'entity_id'         => $options['entity_id'] ?? 1,
        ]);

        return $db->lastInsertId('payment_coupon_item');
    }

    public function createCouponUsage(Connection $db, array $options = [])
    {
        $db->insert('payment_coupon_usage', [
            'coupon_id'         => $options['coupon_id'] ?? 1,
            'transaction_id'    => $options['transaction_id'] ?? 1,
            'user_id'           => $options['user_id'] ?? 1,
            'created'           => $options['created'] ?? time(),
        ]);

        return $db->lastInsertId('payment_coupon_usage');
    }
}
