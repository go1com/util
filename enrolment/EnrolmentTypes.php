<?php

namespace go1\util\enrolment;

class EnrolmentTypes
{
    const TYPE_ENROLMENT         = 'enrolment'; // Real enrolment in GO1, data in gc_enrolment table.
    const TYPE_MANUAL_RECORD     = 'manual-record'; // Enrolment from outside of GO1, need to be reviewed to become real enrolment, data in enrolment_manual table.
    const TYPE_PLAN_ASSIGNED     = 'plan-assigned'; // Bookmark of manager or portal admin for learner to learn in future, data in gc_plan table.
    const TYPE_AWARD             = 'award'; // Group of courses, awards with a rule so that learner can achieve it, data in award_enrolment table.
    const TYPE_AWARD_ITEM        = 'award-item'; // Each items' enrolment of the above
    const TYPE_VIRTUAL_ENROLMENT = 'virtual'; // Not started enrolment created for a learner in a learning object, there are no data in database.

    const ALL = [self::TYPE_ENROLMENT, self::TYPE_MANUAL_RECORD, self::TYPE_PLAN_ASSIGNED, self::TYPE_AWARD, self::TYPE_AWARD_ITEM, self::TYPE_VIRTUAL_ENROLMENT];
}
