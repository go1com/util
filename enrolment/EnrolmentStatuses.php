<?php

namespace go1\util\enrolment;

use Doctrine\DBAL\Connection;
use go1\util\edge\EdgeTypes;
use go1\util\lo\LoTypes;
use InvalidArgumentException;
use stdClass;

class EnrolmentStatuses
{
    # Pre-enrolment statuses
    # ---------------------
    # const PENDING                  = -1;
    const ASSIGNED                 = -3; # Someone added this for you to do
    const ENQUIRED                 = -4; #
    const MANUAL_COMPLETE          = -5;
    const MANUAL_COMPLETE_VERIFIED = -6;

    # Enrolment statuses
    # ---------------------
    const NOT_STARTED = 'not-started'; # you have enrolled but not yet opened the course
    const IN_PROGRESS = 'in-progress'; # you are learning the LO.
    const PENDING     = 'pending';     # you have enrolled but the enrolment need to be reviewed or blocked by other enrolment
    const COMPLETED   = 'completed';   # you get this state when you finish the course
    const EXPIRED     = 'expired';     # your enrolment was completed, but it's expired.

    // Numeric values for the statuses. Being used in ES.
    const I_ASSIGNED    = -3;
    const I_NOT_STARTED = -2;
    const I_PENDING     = -1;
    const I_IN_PROGRESS = 1;
    const I_EXPIRED     = 2;
    const I_COMPLETED   = 100;

    /**
     * All available values that user can input.
     * Expired is only set by our background logic.
     */
    public static function all()
    {
        return [self::ASSIGNED, self::NOT_STARTED, self::IN_PROGRESS, self::PENDING, self::COMPLETED];
    }

    public static function toNumeric(string $status): int
    {
        switch ($status) {
            case self::ASSIGNED:
                return self::I_ASSIGNED;

            case self::NOT_STARTED:
                return self::I_NOT_STARTED;

            case self::PENDING:
                return self::I_PENDING;

            case self::IN_PROGRESS:
                return self::I_IN_PROGRESS;

            case self::EXPIRED:
                return self::I_EXPIRED;

            case self::COMPLETED:
                return self::I_COMPLETED;

            default:
                throw new InvalidArgumentException('Unknown enrolment status: ' . $status);
        }
    }

    public static function defaultStatus(Connection $db, int $profileId, stdClass $lo, string $input = self::IN_PROGRESS)
    {
        // Mark status is "pending" enrolment If a user enrolls to a dependency module.
        if (LoTypes::MODULE === $lo->type) {
            $moduleId = 'SELECT target_id FROM gc_ro WHERE source_id = ? AND type = ?';
            $moduleId = $db->fetchColumn($moduleId, [$lo->id, EdgeTypes::HAS_MODULE_DEPENDENCY]);
            if ($moduleId) {
                if (!$enrolmentId = EnrolmentHelper::enrolmentId($db, $moduleId, $profileId)) {
                    return self::PENDING;
                }
                else {
                    $enrolment = EnrolmentHelper::load($db, $enrolmentId);
                    if (EnrolmentStatuses::COMPLETED != $enrolment->status) {
                        return self::PENDING;
                    }
                }
            }
        }

        // GO1P-6926: If there's a scheduling, user can't start the LO instantly.
        $schedule = 'SELECT 1 FROM gc_ro WHERE type = ? AND source_id = ? AND target_id > ?';
        $schedule = $db->fetchColumn($schedule, [EdgeTypes::PUBLISH_ENROLMENT_LO_START_BASE, $lo->id, time()]);
        if ($schedule) {
            return self::PENDING;
        }

        return $input;
    }
}
