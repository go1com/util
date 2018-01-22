<?php

namespace go1\util\activity;

use ReflectionClass;

class ActivityType
{
    const TYPE_LO                    = 'lo';
    const TYPE_AWARD                 = 'award';
    const TYPE_AWARD_ENROLMENT       = 'award_enrolment';
    const TYPE_ENROLMENT             = 'enrolment';
    const TYPE_EDGE                  = 'ro';
    const TYPE_USER                  = 'user';
    const TYPE_ASSIGNMENT_FEEDBACK   = 'assignment_feedback';
    const TYPE_ASSIGNMENT_SUBMISSION = 'assignment_submission';
    const TYPE_ACCOUNT               = 'account';
    const TYPE_GROUP                 = 'group';
    const TYPE_NOTE                  = 'note';
    const TYPE_GROUP_ITEM            = 'group_item';
    const TYPE_PORTAL                = 'portal';
    const TYPE_PLAN                  = 'plan';
    const TYPE_COUPON                = 'coupon';
    const TYPE_ECK_METADATA          = 'eck_metadata';
    const TYPE_ECK_ENTITY            = 'eck_entity';
    const TYPE_CREDIT                = 'credit';
    const TYPE_ENROLMENT_MANUAL      = 'enrolment_manual';
    const TYPE_CONTRACT              = 'contract';
    const TYPE_VOTE                  = 'vote';

    public static function all()
    {
        $rSelf = new ReflectionClass(__CLASS__);

        $values = [];
        foreach ($rSelf->getConstants() as $const) {
            if (is_scalar($const)) {
                $values[] = $const;
            }
        }

        return $values;
    }
}
