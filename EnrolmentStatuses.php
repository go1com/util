<?php

namespace go1\util;

use Doctrine\DBAL\Connection;
use stdClass;

class EnrolmentStatuses
{
    const ASSIGNED    = 'assigned';    # Someone added this for you to do
    const NOT_STARTED = 'not-started'; # you have enrolled but not yet opened the course
    const IN_PROGRESS = 'in-progress'; # you are learning the LO.
    const PENDING     = 'pending';     # you have enrolled but the enrolment need to be reviewed or blocked by other enrolment
    const COMPLETED   = 'completed';   # you get this state when you finish the course
    const EXPIRED     = 'expired';     # your enrolment was completed, but it's expired.

    /**
     * All available values that user can input.
     * Expired is only set by our background logic.
     */
    public static function all()
    {
        return [self::ASSIGNED, self::NOT_STARTED, self::IN_PROGRESS, self::PENDING, self::COMPLETED];
    }

    public static function defaultStatus(Connection $db, int $profileId, stdClass $lo, string $input = self::IN_PROGRESS)
    {
        // Mark status is "pending" enrolment If a user enrolls to a dependency module.
        if (LoTypes::MODULE === $lo->type) {
            $moduleId = 'SELECT target_id FROM gc_ro WHERE source_id = ? AND type = ?';
            $moduleId = $db->fetchColumn($moduleId, [$lo->id, EdgeTypes::HAS_MODULE_DEPENDENCY]);
            if ($moduleId) {
                if (!$enrolmentId = EnrolmentHelper::enrolmentId($db, $lo->id, $profileId)) {
                    return self::PENDING;
                }
            }
        }

        // GO1P-6926: If there's a scheduling, user can't start the LO instantly.
        $tenMinutes = strtotime('- 10 minutes');
        $schedule = 'SELECT 1 FROM gc_ro WHERE type = ? AND source_id = ? AND target_id <= ?';
        $schedule = $db->fetchColumn($schedule, [EdgeTypes::PUBLISH_ENROLMENT_LO_START_BASE, $lo->id, $tenMinutes]);
        if ($schedule) {
            return self::PENDING;
        }

        return $input;
    }
}
