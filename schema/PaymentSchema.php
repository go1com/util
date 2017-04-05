<?php

namespace go1\util\schema;

use Doctrine\DBAL\Schema\Schema;

class PaymentSchema
{
    public static function install(Schema $schema)
    {
        // Custom index for each portal.
        $localTransactionId = $schema->createTable('payment_portal_id');
        $localTransactionId->addColumn('transaction_id', 'integer', ['unsigned' => true]);
        $localTransactionId->addColumn('local_id', 'integer');
        $localTransactionId->setPrimaryKey(['transaction_id']);

        $txn = $schema->createTable('payment_transaction');
        $txn->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
        $txn->addColumn('email', 'string');
        $txn->addColumn('status', 'integer');
        $txn->addColumn('amount', 'float');
        $txn->addColumn('currency', 'string');
        $txn->addColumn('data', 'blob', ['notnull' => false]);
        $txn->addColumn('created', 'integer', ['unsigned' => true, 'notnull' => false]);
        $txn->addColumn('updated', 'integer', ['unsigned' => true, 'notnull' => false]);
        $txn->addColumn('payment_method', 'string', ['notnull' => false]);
        $txn->setPrimaryKey(['id']);
        $txn->addIndex(['status']);
        $txn->addIndex(['email']);
        $txn->addIndex(['created']);
        $txn->addIndex(['updated']);
        $txn->addIndex(['payment_method']);

        $item = $schema->createTable('payment_transaction_items');
        $item->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
        $item->addColumn('transaction_id', 'integer', ['unsigned' => true]);
        $item->addColumn('product_type', 'string');
        $item->addColumn('product_id', 'string');
        $item->addColumn('qty', 'integer');
        $item->addColumn('price', 'float');
        $item->addColumn('tax', 'float', ['default' => 0.00]);
        $item->addColumn('tax_included', 'smallint', ['default' => true]);
        $item->addColumn('data', 'blob');
        $item->setPrimaryKey(['id']);
        $item->addForeignKeyConstraint('payment_transaction', ['transaction_id'], ['id']);

        # stripe_session
        # ---------------------
        # uuid: UUID of user, this will be connection.uuid when the session is completed successfully.
        # state: Avoid unexpected injection.
        # timestamp: The created timestamp, useful to cleanup old sessions.
        $session = $schema->createTable('stripe_session');
        $session->addColumn('uuid', 'string');
        $session->addColumn('state', 'string');
        $session->addColumn('timestamp', 'integer');
        $session->addIndex(['uuid']);
        $session->addIndex(['state']);
        $session->addIndex(['timestamp']);
        $session->addUniqueIndex(['uuid']);

        # stripe_connection
        # ---------------------
        # uuid: The UUID of connection. Should be instance public key.
        # code: OAuth code, on success connection, Stripe redirect user to /CALLBACK?scope=read_write&code=…
        # data: The full object returned from /oauth/token.
        $connection = $schema->createTable('stripe_connection');
        $connection->addColumn('uuid', 'string');
        $connection->addColumn('code', 'string');
        $connection->addColumn('data', 'blob');
        $connection->setPrimaryKey(['uuid']);
        $connection->addIndex(['code']);

        $cart = $schema->createTable('payment_cart');
        $cart->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
        $cart->addColumn('data', 'blob');
        $cart->addColumn('timestamp', 'integer');
        $cart->setPrimaryKey(['id']);
        $cart->addIndex(['timestamp']);

        $item = $schema->createTable('payment_cart_items');
        $item->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
        $item->addColumn('cart_id', 'integer', ['unsigned' => true]);
        $item->addColumn('product_type', 'string', ['description' => 'Can store product and other items, e.g. coupon, fee, …']);
        $item->addColumn('product_id', 'string');
        $item->addColumn('qty', 'integer', ['unsigned' => true]);
        $item->addColumn('price', 'float');
        $item->addColumn('tax', 'float', ['default' => 0.00]);
        $item->addColumn('tax_included', 'smallint', ['default' => true]);
        $item->setPrimaryKey(['id']);
        $item->addForeignKeyConstraint('payment_cart', ['cart_id'], ['id']);

        $customer = $schema->createTable('payment_customer');
        $customer->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
        $customer->addColumn('user_id', 'integer', ['unsigned' => true]);
        $customer->addColumn('customer_id', 'string');
        $customer->addColumn('token', 'string');
        $customer->addColumn('status', 'integer');
        $customer->addColumn('description', 'string');
        $customer->addColumn('metadata', 'text');
        $customer->addColumn('created', 'integer');
        $customer->addColumn('updated', 'integer');
        $customer->setPrimaryKey(['id']);
        $customer->addIndex(['user_id']);
        $customer->addIndex(['customer_id']);
        $customer->addIndex(['token']);
        $customer->addIndex(['status']);
        $customer->addIndex(['created']);
        $customer->addIndex(['updated']);
    }
}
