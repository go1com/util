<?php

namespace go1\util\edge;

/**
 * T: Target
 * S: Source
 * W: Weight. There are hacks around this property, it's not always "weight" as its name.
 * N: Note
 */
class EdgeTypes
{
    # @deprecated
    # ---------------------
    # These types for old LO sharing logic, we will soon drop these types.
    const HAS_SHARE_WITH           = 507; # T: Role ID            | S: Learning object
    const HAS_SHARE_USER_NOTE      = 600; # T: gc_note.id         | S: gc_user.id
    const HAS_SHARE_WITH_LO_USER   = 601; # T: gc_lo.id           | S: gc_user.id
    const HAS_SHARE_WITH_LO_PORTAL = 603; # T: gc_instance.id     | S: Learning object

    const LearningObjectTree = [
        'all'              => [self::HAS_LP_ITEM, self::HAS_MODULE, self::HAS_ELECTIVE_LO, self::HAS_LI, self::HAS_ELECTIVE_LI],
        'learning_pathway' => [self::HAS_LP_ITEM],
        'course'           => [self::HAS_MODULE, self::HAS_ELECTIVE_LO],
        'module'           => [self::HAS_LI, self::HAS_ELECTIVE_LI],
    ];

    const LO_HAS_LO = [
        self::HAS_LP_ITEM,
        self::HAS_LI,
        self::HAS_WORKSHOP,
        self::HAS_MODULE,
        self::HAS_ELECTIVE_LO,
        self::HAS_ELECTIVE_LI,
        self::GROUP_HAS_ITEM,
    ];

    const LO_HAS_CHILDREN = [
        self::HAS_LP_ITEM,
        self::HAS_MODULE,
        self::HAS_ELECTIVE_LO,
        self::HAS_LI,
        self::HAS_ELECTIVE_LI,
    ];

    # Edges which user object is the source
    const USER_HAS_OBJECT = [
        self::HAS_ROLE,
        self::HAS_ACCOUNT,
        self::HAS_MANAGER,
        self::HAS_EMAIL,
        self::HAS_FOLLOWING,
        self::HAS_PORTAL_EDGE,
        self::HAS_SHARE_USER_NOTE,
        self::HAS_SHARE_WITH_LO_USER,
        self::HAS_MENTION,
        self::HAS_ASSIGN,
        self::HAS_LO_ASSIGNMENT,
    ];

    # Edges which user object is the target
    const USER_BELONG_TO = [
        self::HAS_ACCOUNT,
        self::HAS_TUTOR_EDGE,
        self::HAS_AUTHOR_EDGE,
        self::HAS_MANAGER,
        self::HAS_TUTOR_ENROLMENT_EDGE,
        self::HAS_FOLLOWING,
    ];

    # Event relationships
    # - Course => HAS_LI => LI(type = event) => HAS_EVENT => gc_event
    # - Course/Module => HAS_EVENT => gc_event

    # Learning object relationships
    # ---------------------
    const HAS_LP_ITEM                 = 1;  # T: ?                    | S: Learning object (LP only)
    const HAS_PRODUCT                 = 2;  # T: ?                    | S: Learning object
    const HAS_EVENT                   = 3;  # T: Simple event         | S: Learning object (course, module, li type event)
    const HAS_TAG                     = 4;  # T: Tag                  | S: Learning object
    const HAS_LI                      = 5;  # T: ?                    | S: Learning object (module only, course if li type event)
    const HAS_WORKSHOP                = 6;  # T: ?                    | S: ?
    const HAS_MODULE                  = 7;  # T: gc_lo.id             | S: gc_lo.id
    const HAS_ELECTIVE_LO             = 8;  # T: ?                    | S: ?
    const HAS_ELECTIVE_LI             = 9;  # T: ?                    | S: ?
    const HAS_STRIPE_CUSTOMER         = 10; # T: ?                    | S: ?
    const HAS_MODULE_DEPENDENCY       = 11; # T: gc_lo.id             | S: gc_lo.id
    const HAS_CUSTOM_TAG              = 12; # T: Tag                  | S: Learning object
    const HAS_PARENT_TAG              = 13; # T: Tag                  | S: Tag
    const HAS_COUPON                  = 14; # T: ?                    | S: ?
    const HAS_TUTOR                   = 15; # T: Simple Account       | S: Learning object
    const HAS_AUTHOR                  = 17; # T: Simple Account       | S: Learning object
    const HAS_TUTOR_ENROLMENT         = 18; # T: Simple account       | S: Enrolment
    const HAS_ENQUIRY                 = 19; # T: Learning object      | S: Profile
    const HAS_ARCHIVED_ENQUIRY        = 20; # T: NULL                 | S: Deleted gc_ro type HAS_ENQUIRY's id - just for handling duplicated archived enquiries
    const HAS_EXCLUDED_TAG            = 31; # T: Tag                  | S: Learning object
    const COURSE_ASSESSOR             = 32; # T: gc_user.id           | S: Learning object
    const HAS_EVENT_EDGE              = 34; # T: gc_event.id          | S: gc_lo.id (lo.course | lo.module | li.event)
    const HAS_GROUP_EDGE              = 35; # T: gc_social_group.id   | S: gc_user.id
    const AWARD_HAS_ITEM              = 36; # T: LO                   | S: award.                | data: { qty: INTEGER }
    const HAS_CREDIT_REQUEST          = 37; # T: User (learner)       | S: LO                    | Weight: Manager ID — who will review.
    const HAS_CREDIT_REQUEST_DONE     = 38; # T: User (learner)       | S: LO                    | Weight: Manager ID — who paid.
    const HAS_CREDIT_REQUEST_REJECTED = 39; # T: User (learner)       | S: LO                    | Weight: Manager ID — who reject.
    const HAS_LOCATION                = 40; # T: gc_location.id       | S: gc_event.id
    const HAS_LO_LOCATION             = 41; # T: gc_location.id       | S: gc_lo.id
    const HAS_LO_CUSTOMISATION        = 42; # T: gc_instance.id       | S: gc_lo.id              | data: { KEY: VALUE }
    const HAS_AWARD_LOCATION          = 43; # T: gc_location.id       | S: award_award.id
    const HAS_SUGGESTED_COMPLETION    = 44; # T: 0                    | S: gc_ro.id              | data: { KEY: VALUE }
    const AWARD_ASSESSOR              = 45; # T: Account              | S: award.id
    const GROUP_HAS_ITEM              = 46; # T: Learning object      | S: Learning object (group lo only)

    # LO & enrolment scheduling
    # ---------------------
    const  HAS_ENROLMENT_EXPIRATION               = 21; # T: = self.SOURCE | S: Edge (hasLO, hasElectiveLO -- source: LO | target: LO) | NOTE: SOURCE = TARGET to make sure there's no duplication.
    const  SCHEDULE_EXPIRE_ENROLMENT              = 22; # T: Timestamp     | S: Enrolment
    const  SCHEDULE_EXPIRE_ENROLMENT_DONE         = 23; # T: Timestamp     | S: Enrolment  | N: SCHEDULE_EXPIRE_ENROLMENT record will be converted to this when it's processed.
    const  SCHEDULE_UNLOCK_LO                     = 24; # T: Timestamp     | S: LO         | N: See GO1P-6926
    const  SCHEDULE_UNLOCK_LO_DONE                = 25; # T: Timestamp     | S: LO         | N: SCHEDULE_UNLOCK_LO record will be converted to this when it's processed.
    const  PUBLISH_ENROLMENT_LO_START_BASE        = 26; # T: Timestamp     | S: LO         | N: See GO1P-6926
    const  PUBLISH_ENROLMENT_LO_START_BASE_DONE   = 27; # T: Timestamp     | S: Enrolment  | N: HAS_LO_PUBLISH_ENROLMENT record will be converted to this when it's processed.
    const  PUBLISH_ENROLMENT_SELF_START_BASE_CNF  = 28; # T: = self.SOURCE | S: LO         | N: type data structure { interval: string }
    const  PUBLISH_ENROLMENT_SELF_START_BASE      = 29; # T: Timestamp     | S: Enrolment  | N: See GO1P-6926
    const  PUBLISH_ENROLMENT_SELF_START_BASE_DONE = 30; # T: Timestamp     | S: Enrolment  | N: PUBLISH_ENROLMENT_SELF_START_BASE record will be coverted to this when it's processed.
    const  PUBLISH_MARKETPLACE_REQUEST_REJECTED   = 50; # T: User-Rejecter | S: LO         | Learning object
    const  PUBLISH_MARKETPLACE_REQUEST_APPROVED   = 51; # T: User-Acceptor | S: LO         | Learning object
    const  PUBLISH_MARKETPLACE_SCHEDULED          = 52; # T: Timestamp     | S: LO         | Data contains whether publishing to marketplace, which groups to join to, and the flag which should be resolved

    # Award relationships
    # ---------------------
    const  AWARD_PUBLISH_MARKETPLACE_REQUEST_REJECTED = 60; # T: Timestamp     | S: Award      | W: gc_user.id (Rejecter)
    const  AWARD_PUBLISH_MARKETPLACE_REQUEST_APPROVED = 61; # T: Timestamp     | S: Award      | W: gc_user.id (Acceptor)

    # Portal relationships
    # ---------------------
    const HAS_DOMAIN = 16;

    # User relationships
    # ---------------------
    const HAS_ROLE                       = 500; # T: Role               | S: User
    const HAS_ACCOUNT                    = 501; # T: Account            | S: User
    const HAS_TUTOR_EDGE                 = 502; # T: User (Tutor)       | S: gc_ro id - the record has source_id is course, target_id is (Module)
    const HAS_AUTHOR_EDGE                = 503; # T: User               | S: Learning object
    const HAS_MANAGER                    = 504; # T: User Id of manager | S: gc_user.id Account Id of student
    const HAS_EMAIL                      = 505; # T: gc_user_mail id    | S: gc_user id
    const HAS_TUTOR_ENROLMENT_EDGE       = 506; # T: gc_enrolment id    | S: gc_user id
    const HAS_FOLLOWING                  = 508; # T: gc_user.id         | S: gc_user.id
    const HAS_PORTAL_EDGE                = 509; # T: gc_instance.id     | S: gc_user.id | Portal - Owner Relationship
    const HAS_ACCOUNT_VIRTUAL            = 510; # T: User               | S: Account
    const HAS_MENTION                    = 602; # T: gc_lo.id           | S: gc_user.id
    const HAS_SHARE_GROUP_NOTE           = 604; # T: gc_social_group.id | S: gc_note.id
    const HAS_SHARE_PORTAL_NOTE          = 605; # T: gc_note.id         | S: gc_instance.id
    const HAS_NOTE                       = 606; # T: gc_note.id         | S: gc_user.id
    const HAS_MANUAL_PAYMENT             = 607; # T: submitted times    | S: lo.id | W: gc_user.id - we use weight to store user.id to avoid the table constrain, target_id to store the number of submitted times
    const HAS_MANUAL_PAYMENT_ACCEPT      = 608; # T: submitted times    | S: lo.id | W: gc_user.id
    const HAS_MANUAL_PAYMENT_REJECT      = 609; # T: submitted times    | S: lo.id | W: gc_user.id
    const HAS_REQUEST_GROUP              = 610; # T: submitted times    | S: group.id | W: gc_user.id - we use weight to store user.id to avoid the table constrain, target_id to store the number of submitted times
    const HAS_REQUEST_GROUP_ACCEPT       = 611; # T: submitted times    | S: group.id | W: gc_user.id
    const HAS_REQUEST_GROUP_REJECT       = 612; # T: submitted times    | S: group.id | W: gc_user.id
    const HAS_REQUEST_GROUP_BLOCK        = 613; # T: submitted times    | S: group.id | W: gc_user.id
    const HAS_ASSIGN                     = 701; # T: enrolment.id       | S: gc_user.id
    const HAS_LO_ASSIGNMENT              = 702; # T: suggested LO       | S: gc_user.id | Weight: Suggesting user.
    const HAS_LO_ASSIGNMENT_ACCEPTED     = 703; # record.HAS_LO_SUGGESTION will be changed to this when suggestion is accepted.
    const HAS_LO_ASSIGNMENT_REJECTED     = 704; # record.HAS_LO_SUGGESTION will be changed to this when suggestion is rejected.
    const HAS_LO_ASSIGNMENT_DUE_DATE     = 705; # T: self.SOURCE        | S: suggestion ID | W: Timestamp  | N: See GO1P-8097
    const CREDIT_TRANSFER                = 800; # T: Timestamp          | S: credit.id | D: old owner, new owner, actor
    const HAS_PLAN                       = 900; # T: gc_plan.id         | S: enrolment.id
    const HAS_AWARD_TUTOR_ENROLMENT_EDGE = 511; # T: award_enrolment.id | S: gc_user.id

    # Group relationships
    const HAS_GROUP_SYSTEM          = 1000; # T: Group | S: Portal
    const HAS_GROUP_CONTENT_SHARING = 1001; # T: Group | S: LO

    const HAS_ORIGINAL_ENROLMENT    = 1002; # T: Original enrolment_id | S: Clone enrolment_id
}
