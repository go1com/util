<?php

namespace go1\util\schema\mock;

use Doctrine\DBAL\Connection;

class BumpMock
{
    public function callback(Connection $db, $table)
    {
        return function () use ($db, $table) {
            return $db->fetchColumn("SELECT MAX(id) FROM {$table}") + 1;
        };
    }
}
