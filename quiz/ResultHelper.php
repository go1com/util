<?php

namespace go1\util\quiz;

use Doctrine\DBAL\Connection;
use go1\util\DB;

class ResultHelper
{
    public static function load(Connection $db, int $id)
    {
        $result = $db
            ->executeQuery('SELECT * FROM result WHERE result_id = ?', [$id])
            ->fetch(DB::OBJ);

        return $result;
    }

    public static function getSubmittedDate(Connection $db, int $personId, string $quizRuuid)
    {
        $submittedDate = $db
            ->executeQuery('SELECT time_start FROM result WHERE taker = ? AND quiz_ruuid = ? ORDER BY result_id DESC', [$personId, $quizRuuid])
            ->fetch(DB::COL);

        return $submittedDate;
    }

    public static function getMarkedDate(Connection $db, int $personId, string $quizRuuid)
    {
        $result = $db
            ->executeQuery('SELECT time_end, is_evaluated FROM result WHERE taker = ? AND quiz_ruuid = ? ORDER BY result_id DESC', [$personId, $quizRuuid])
            ->fetch(DB::OBJ);

        return !empty($result->is_evaluated) ? $result->time_end : null;
    }
}
