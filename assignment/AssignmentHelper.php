<?php

namespace go1\util\assignment;

use Doctrine\DBAL\Connection;
use go1\util\DB;
use go1\util\enrolment\EnrolmentHelper;
use go1\util\lo\LiTypes;
use go1\util\lo\LoHelper;

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

    public static function locateLiAssignment(Connection $go1, int $assignmentId) {
        $liId = 'SELECT id FROM gc_lo WHERE remote_id = ? AND type = ?';
        $liId = $go1->fetchColumn($liId, [$assignmentId, LiTypes::ASSIGNMENT]);

        if ($liId) {
            return LoHelper::load($go1, $liId);
        }

        return null;
    }

    public static function getEnrolment(Connection $go1, int $studentProfileId, int $assignmentId, int $moduleId = null)
    {
        $liAssignment = self::locateLiAssignment($go1, $assignmentId);
        if ($liAssignment) {
            return $enrolment = EnrolmentHelper::loadByLoAndProfileId($go1, $liAssignment->id, $studentProfileId, $moduleId);
        }

        return null;
    }
}
