<?php

namespace go1\util\quiz;

use Doctrine\DBAL\Connection;
use go1\util\DB;

class AnswerHelper
{
    public static function load(Connection $db, int $id)
    {
        $answer = $db
            ->executeQuery('SELECT * FROM answer WHERE answer_id = ?', [$id])
            ->fetch(DB::OBJ);

        return $answer;
    }

    public static function loadByQuestionUuid(Connection $db, string $questionUuid)
    {
        $answer = $db
            ->executeQuery('SELECT * FROM answer WHERE question_uuid = ?', [$questionUuid])
            ->fetch(DB::OBJ);

        return $answer;
    }
}
