<?php

namespace go1\util\assignment;

use Doctrine\DBAL\Connection;
use go1\util\DB;

class SubmissionHelper
{
    public static function load(Connection $db, int $id)
    {
        $submission = $db
            ->executeQuery('SELECT * FROM asm_submission WHERE id = ?', [$id])
            ->fetch(DB::OBJ);

        return $submission;
    }

    public static function loadByEnrolmentId(Connection $db, int $enrolmentId)
    {
        $submission = $db
            ->executeQuery('SELECT * FROM asm_submission WHERE enrolment_id = ?', [$enrolmentId])
            ->fetch(DB::OBJ);

        return $submission;
    }

    public static function getSubmittedDate(Connection $db, int $submissionId)
    {
        $submittedDate = $db
            ->executeQuery('SELECT MAX(created) FROM asm_submission_revision WHERE submission_id = ?', [$submissionId])
            ->fetch(DB::COL);

        return $submittedDate;
    }

    public static function getMarkedDate(Connection $db, int $enrolmentId, int $userId)
    {
        $markedDate = $db
            ->executeQuery('SELECT MAX(updated) FROM asm_submission_revision WHERE submission_id = ? AND actor_id > 1 AND actor_id != ?', [$submissionId, $userId])
            ->fetch(DB::COL);

        return $markedDate;
    }
}
