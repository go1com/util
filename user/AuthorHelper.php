<?php

namespace go1\util\user;

use Doctrine\DBAL\Connection;
use go1\util\edge\EdgeTypes;
use PDO;

class AuthorHelper
{
    public static function authorIds(Connection $db, int $loId): array
    {
        $sql = 'SELECT ro.target_id FROM gc_ro ro ';
        $sql .= 'WHERE ro.source_id = ? AND ro.type = ?';

        return $db->executeQuery($sql, [$loId, EdgeTypes::HAS_AUTHOR_EDGE])->fetchAll(PDO::FETCH_COLUMN);
    }
}
