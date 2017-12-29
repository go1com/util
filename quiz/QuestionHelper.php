<?php

namespace go1\util\quiz;

use Doctrine\DBAL\Connection;
use go1\util\DB;

class QuestionHelper
{
    public static function load(Connection $db, int $id)
    {
        $question = $db
            ->executeQuery('SELECT * FROM question WHERE li_id = ?', [$id])
            ->fetch(DB::OBJ);

        return $question;
    }
}
