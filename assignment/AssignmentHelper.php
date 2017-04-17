<?php

namespace go1\util\assignment;

use Doctrine\DBAL\Connection;
use go1\util\DB;

class AssignmentHelper
{
    public static function load(Connection $db, int $id)
    {
        return $db
            ->executeQuery('SELECT * FROM asm_assignment WHERE id = ?', [$id])
            ->fetch(DB::OBJ);
    }
}
