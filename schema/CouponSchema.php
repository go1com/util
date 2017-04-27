<?php

namespace go1\util\schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;

class CouponSchema
{
    public static function install(Schema $schema)
    {
        if ($schema->hasTable('payment_coupon')) {
            $coupon = $schema->createTable('payment_coupon');
            $coupon->addColumn('id', Type::INTEGER, ['unsigned' => true, 'autoincrement' => true]);
            $coupon->addColumn('code', Type::STRING);
            $coupon->addColumn('instance_id', Type::INTEGER, ['unsigned' => true]);
            $coupon->addColumn('entity_type', Type::STRING);
            $coupon->addColumn('entity_id', Type::INTEGER, ['unsigned' => true]);
            $coupon->addColumn('user_id', Type::INTEGER, ['unsigned' => true]);
            $coupon->addColumn('type', Type::SMALLINT, ['unsigned' => true]);
            $coupon->addColumn('value', Type::FLOAT, ['unsigned' => true]);
            $coupon->addColumn('status', Type::SMALLINT, ['description' => '-1: No longer available/Reach limitation. 0: Unpublished. 1: Still available.']);
            $coupon->addColumn('limitation', Type::INTEGER, ['unsigned' => true]);
            $coupon->addColumn('expiration', Type::DATETIMETZ);
            $coupon->addColumn('created', Type::INTEGER);
            $coupon->addColumn('updated', Type::INTEGER);
            $coupon->setPrimaryKey(['coupon']);
            $coupon->addUniqueIndex(['code']);
            $coupon->addIndex(['instance_id']);
            $coupon->addIndex(['entity_type']);
            $coupon->addIndex(['entity_id']);
            $coupon->addIndex(['status']);
            $coupon->addIndex(['created']);
            $coupon->addIndex(['updated']);
        }

        if ($schema->hasTable('payment_coupon_usage')) {
            $couponUsage = $schema->createTable('payment_coupon_usage');
            $couponUsage->addColumn('id', Type::INTEGER, ['unsigned' => true, 'autoincrement' => true]);
            $couponUsage->addColumn('coupon_id', Type::INTEGER, ['unsigned' => true]);
            $couponUsage->addColumn('transaction_id', Type::INTEGER, ['unsigned' => true]);
            $couponUsage->addColumn('user_id', Type::INTEGER, ['unsigned' => true]);
            $couponUsage->addColumn('entity_type', Type::STRING);
            $couponUsage->addColumn('entity_id', Type::INTEGER, ['unsigned' => true]);
            $couponUsage->addIndex(['coupon_id']);
            $couponUsage->addIndex(['transaction_id']);
            $couponUsage->addIndex(['user_id']);
            $couponUsage->addIndex(['entity_type']);
            $couponUsage->addIndex(['entity_id']);
            $couponUsage->addIndex(['created']);
            $couponUsage->addIndex(['updated']);
            $couponUsage->addForeignKeyConstraint('payment_coupon', ['coupon_id'], ['id']);
        }
    }
}
