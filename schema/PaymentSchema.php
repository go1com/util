<?php

namespace go1\util\schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;

class PaymentSchema
{
    public static function install(Schema $schema)
    {
        // Custom index for each portal.
        if (!$schema->hasTable('payment_local_id')) {
            $localTransactionId = $schema->createTable('payment_local_id');
            $localTransactionId->addColumn('transaction_id', Type::INTEGER, ['unsigned' => true]);
            $localTransactionId->addColumn('instance_id', Type::INTEGER, ['unsigned' => true]);
            $localTransactionId->addColumn('local_id', Type::INTEGER, ['unsigned' => true]);
            $localTransactionId->setPrimaryKey(['transaction_id']);
            $localTransactionId->addUniqueIndex(['instance_id', 'local_id']);
            $localTransactionId->addIndex(['instance_id']);
            $localTransactionId->addIndex(['local_id']);
        }

        if (!$schema->hasTable('payment_transaction')) {
            $txn = $schema->createTable('payment_transaction');
            $txn->addColumn('id', Type::INTEGER, ['unsigned' => true, 'autoincrement' => true]);
            $txn->addColumn('email', Type::STRING);
            $txn->addColumn('status', Type::INTEGER);
            $txn->addColumn('amount', 'float');
            $txn->addColumn('currency', Type::STRING);
            $txn->addColumn('data', 'blob', ['notnull' => false]);
            $txn->addColumn('created', Type::INTEGER, ['unsigned' => true, 'notnull' => false]);
            $txn->addColumn('updated', Type::INTEGER, ['unsigned' => true, 'notnull' => false]);
            $txn->addColumn('payment_method', Type::STRING, ['notnull' => false]);
            $txn->setPrimaryKey(['id']);
            $txn->addIndex(['status']);
            $txn->addIndex(['email']);
            $txn->addIndex(['created']);
            $txn->addIndex(['updated']);
            $txn->addIndex(['payment_method']);
        }

        if (!$schema->hasTable('payment_transaction_items')) {
            $item = $schema->createTable('payment_transaction_items');
            $item->addColumn('id', Type::INTEGER, ['unsigned' => true, 'autoincrement' => true]);
            $item->addColumn('transaction_id', Type::INTEGER, ['unsigned' => true]);
            $item->addColumn('product_type', Type::STRING);
            $item->addColumn('product_id', Type::STRING);
            $item->addColumn('qty', Type::INTEGER);
            $item->addColumn('price', 'float');
            $item->addColumn('tax', 'float', ['default' => 0.00]);
            $item->addColumn('tax_included', 'smallint', ['default' => true]);
            $item->addColumn('data', 'blob');
            $item->setPrimaryKey(['id']);
            $item->addForeignKeyConstraint('payment_transaction', ['transaction_id'], ['id']);
        }

        # stripe_session
        # ---------------------
        # uuid: UUID of user, this will be connection.uuid when the session is completed successfully.
        # state: Avoid unexpected injection.
        # timestamp: The created timestamp, useful to cleanup old sessions.
        if (!$schema->hasTable('stripe_session')) {
            $session = $schema->createTable('stripe_session');
            $session->addColumn('uuid', Type::STRING);
            $session->addColumn('state', Type::STRING);
            $session->addColumn('timestamp', Type::INTEGER);
            $session->addIndex(['uuid']);
            $session->addIndex(['state']);
            $session->addIndex(['timestamp']);
            $session->addUniqueIndex(['uuid']);
        }

        # stripe_connection
        # ---------------------
        # uuid: The UUID of connection. Should be instance public key.
        # code: OAuth code, on success connection, Stripe redirect user to /CALLBACK?scope=read_write&code=…
        # data: The full object returned from /oauth/token.
        if (!$schema->hasTable('stripe_connection')) {
            $connection = $schema->createTable('stripe_connection');
            $connection->addColumn('uuid', Type::STRING);
            $connection->addColumn('code', Type::STRING);
            $connection->addColumn('data', 'blob');
            $connection->setPrimaryKey(['uuid']);
            $connection->addIndex(['code']);
        }

        if (!$schema->hasTable('payment_cart')) {
            $cart = $schema->createTable('payment_cart');
            $cart->addColumn('id', Type::INTEGER, ['unsigned' => true, 'autoincrement' => true]);
            $cart->addColumn('data', 'blob');
            $cart->addColumn('timestamp', Type::INTEGER);
            $cart->setPrimaryKey(['id']);
            $cart->addIndex(['timestamp']);
        }

        if (!$schema->hasTable('payment_cart_items')) {
            $item = $schema->createTable('payment_cart_items');
            $item->addColumn('id', Type::INTEGER, ['unsigned' => true, 'autoincrement' => true]);
            $item->addColumn('cart_id', Type::INTEGER, ['unsigned' => true]);
            $item->addColumn('product_type', Type::STRING, ['description' => 'Can store product and other items, e.g. coupon, fee, …']);
            $item->addColumn('product_id', Type::STRING);
            $item->addColumn('qty', Type::INTEGER, ['unsigned' => true]);
            $item->addColumn('price', 'float');
            $item->addColumn('tax', 'float', ['default' => 0.00]);
            $item->addColumn('tax_included', 'smallint', ['default' => true]);
            $item->setPrimaryKey(['id']);
            $item->addForeignKeyConstraint('payment_cart', ['cart_id'], ['id']);
        }

        if (!$schema->hasTable('payment_customer')) {
            $customer = $schema->createTable('payment_customer');
            $customer->addColumn('id', Type::INTEGER, ['unsigned' => true, 'autoincrement' => true]);
            $customer->addColumn('user_id', Type::INTEGER, ['unsigned' => true]);
            $customer->addColumn('customer_id', Type::STRING);
            $customer->addColumn('token', Type::STRING);
            $customer->addColumn('status', Type::INTEGER);
            $customer->addColumn('description', Type::STRING);
            $customer->addColumn('metadata', Type::TEXT);
            $customer->addColumn('created', Type::INTEGER);
            $customer->addColumn('updated', Type::INTEGER);
            $customer->setPrimaryKey(['id']);
            $customer->addIndex(['user_id']);
            $customer->addIndex(['customer_id']);
            $customer->addIndex(['token']);
            $customer->addIndex(['status']);
            $customer->addIndex(['created']);
            $customer->addIndex(['updated']);
        }
    }
}
