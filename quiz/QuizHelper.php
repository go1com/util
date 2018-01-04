<?php
namespace go1\util\quiz;

use Doctrine\DBAL\Connection;
use go1\util\DB;
use go1\util\enrolment\EnrolmentStatuses;
use stdClass;

class QuizHelper
{

    public static function loadResult(Connection $db, int $id)
    {
        return $db
            ->executeQuery('SELECT * FROM result WHERE result_id = ?', [$id])
            ->fetch(DB::OBJ);
    }

    public static function loadByLiId(Connection $db, int $id)
    {
        return $db
            ->executeQuery('SELECT * FROM quiz WHERE li_id = ?', [$id])
            ->fetch(DB::OBJ);
    }

    public static function load(Connection $db, int $id)
    {
        return $db
            ->executeQuery('SELECT * FROM quiz WHERE quiz_id = ?', [$id])
            ->fetch(DB::OBJ);
    }

    public static function questionCount(Connection $db, stdClass $quiz)
    {
        return $db->fetchColumn('SELECT count(*) FROM quiz_questions WHERE quiz_ruuid = ?', [$quiz->ruuid]);
    }

    public static function answerCountByEnrolment(Connection $db, int $enrolmentId)
    {
        $resultUuid = $db->fetchColumn('SELECT uuid FROM result WHERE enrolment_id = ?', [$enrolmentId]);
        return $resultUuid ? static::answerCount($db, $resultUuid) : 0;
    }

    public static function answerCount(Connection $db, string $resultUuid)
    {
        return $db->fetchColumn('SELECT COUNT(sequence_id) FROM sequence WHERE result_uuid = ? AND answer_uuid IS NOT NULL', [$resultUuid]);
    }

    public static function progress(Connection $db, stdClass $quiz, int $enrolmentId)
    {
        $questionNum = static::questionCount($db, $quiz);
        $answerNum  = static::answerCountByEnrolment($db, $enrolmentId);

        return [
            EnrolmentStatuses::COMPLETED  => $answerNum,
            EnrolmentStatuses::PERCENTAGE => ($questionNum > 0) ? round(100 * ($answerNum / $questionNum)) : 0
        ];
    }
}
