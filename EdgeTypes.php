<?php

namespace go1\util;

class EdgeTypes
{
    const HAS_LP_ITEM           = 1;
    const HAS_PRODUCT           = 2;
    const HAS_EVENT             = 3;
    const HAS_TAG               = 4;
    const HAS_LI                = 5;
    const HAS_WORKSHOP          = 6;
    const HAS_MODULE            = 7;
    const HAS_ELECTIVE_LO       = 8;
    const HAS_ELECTIVE_LI       = 9;
    const HAS_STRIPE_CUSTOMER   = 10;
    const HAS_MODULE_DEPENDENCY = 11;
    const HAS_CUSTOM_TAG        = 12;
    const HAS_PARENT_TAG        = 13;
    const HAS_COUPON            = 14;
    const HAS_TUTOR             = 15;
    const HAS_DOMAIN            = 16;

    /**
     * Source: Learning object
     * Target: Simple Account
     */
    const HAS_AUTHOR = 17;

    /**
     * Create enrolment relationship with tutor
     * Source: Enrolment
     * Target: Simple account
     */
    const HAS_TUTOR_ENROLMENT = 18;

    /**
     * Source: Profile
     * Target: Learning object
     */
    const HAS_ENQUIRY = 19;

    const HAS_ROLE    = 500;
    const HAS_ACCOUNT = 501;

    /**
     * Create course module relationship with tutor.
     * Source: gc_ro id - the record has source_id is course, target_id is
     * Target: gc_user id
     */
    const HAS_TUTOR_EDGE = 502;

    /**
     * Source: Learning object
     * Target: gc_user.id
     */
    const HAS_AUTHOR_EDGE = 503;

    /**
     * Source: gc_user.id of student
     * Target: gc_user.id of manager.
     */
    const HAS_MANAGER = 504;

    /**
     * Create user secondary mail relationship
     * Source: gc_user id
     * target_id: gc_user_mail id
     */
    const HAS_EMAIL = 505;

    /**
     * Create enrolment tutor relationship.
     * source_id: gc_user id
     * target_id: gc_enrolment id
     */
    const HAS_TUTOR_ENROLMENT_EDGE = 506;

    /**
     * Source: Learning object
     * Target: Role ID
     */
    const HAS_SHARE_WITH = 507;

    /**
     * Source: gc_user.id
     * Target: gc_user.id
     */
    const HAS_FOLLOWING = 508;

    /**
     * Source: gc_user.id
     * Target: gc_instance.id
     */
    const HAS_PORTAL_EDGE = 509;

    /**
     * Source: gc_user.id
     * Target: gc_lo.id
     */
    const HAS_SHARE_USER_LO = 600;

    /**
     * Source: gc_user.id
     * Target: gc_lo.id
     */
    const HAS_SHARE_WITH_LO_USER = 601;
}
