<?php

namespace go1\util\credit;

use Doctrine\DBAL\Connection;
use go1\util\DB;

class CreditHelper
{
    public static function total(Connection $db, int $ownerId, int $productId, string $productType = 'lo')
    {
        return static::count($db, $ownerId, $productId, $productType, [CreditStatuses::STATUS_AVAILABLE, CreditStatuses::STATUS_USED]);
    }

    public static function used(Connection $db, int $ownerId, int $productId, string $productType = 'lo')
    {
        return static::count($db, $ownerId, $productId, $productType, [CreditStatuses::STATUS_USED]);
    }

    public static function remaining(Connection $db, int $ownerId, int $productId, string $productType = 'lo')
    {
        return static::count($db, $ownerId, $productId, $productType, [CreditStatuses::STATUS_AVAILABLE]);
    }

    public static function count(Connection $db, int $ownerId, int $productId, string $productType = 'lo', array $status = [])
    {
        $sql = 'SELECT COUNT(*) FROM credit WHERE owner_id = ? AND product_type = ? AND product_id = ? AND status IN (?)';
        $params = [$ownerId, $productType, $productId, $status];

        return $db
            ->executeQuery($sql, $params, [DB::INTEGER, DB::STRING, DB::INTEGER, DB::INTEGERS])
            ->fetchColumn();
    }
}
