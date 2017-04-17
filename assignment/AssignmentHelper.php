<?php

namespace go1\util\assignment;

use Doctrine\DBAL\Connection;
use go1\util\DB;

class AssignmentHelper
{
    public static function load(Connection $db, int $id)
    {
        $assignment = $db
            ->executeQuery('SELECT * FROM asm_assignment WHERE id = ?', [$id])
            ->fetch(DB::OBJ);

        if ($assignment) {
            if (!$assignment->data = json_decode($assignment->data)) {
                unset($assignment->data);
            }
        }

        return $assignment;
    }
}
