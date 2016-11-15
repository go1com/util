<?php

namespace go1\util;

class EdgeTypes
{
    const HAS_LP_ITEM              = 1;   # Target: ?                | Source: Learning object (LP only)
    const HAS_PRODUCT              = 2;   # Target: ?                | Source: Learning object
    const HAS_EVENT                = 3;   # Target: ?                | Source: Learning object (course, module?)
    const HAS_TAG                  = 4;   # Target: ?                | Source: Learning object (course only)
    const HAS_LI                   = 5;   # Target: ?                | Source: Learning object (module only)
    const HAS_WORKSHOP             = 6;   # Target: ?                | Source: ?
    const HAS_MODULE               = 7;   # Target: ?                | Source: ?
    const HAS_ELECTIVE_LO          = 8;   # Target: ?                | Source: ?
    const HAS_ELECTIVE_LI          = 9;   # Target: ?                | Source: ?
    const HAS_STRIPE_CUSTOMER      = 10;  # Target: ?                | Source: ?
    const HAS_MODULE_DEPENDENCY    = 11;  # Target: ?                | Source: ?
    const HAS_CUSTOM_TAG           = 12;  # Target: Tag              | Source: Learning object
    const HAS_PARENT_TAG           = 13;  # Target: Tag              | Source: Tag
    const HAS_COUPON               = 14;  # Target: ?                | Source: ?
    const HAS_TUTOR                = 15;  # Target: ?                | Source: ?
    const HAS_DOMAIN               = 16;  # Target: ?                | Source: ?
    const HAS_EXCLUDED_TAG         = 19;  # Target: ?                | Source: ?
    const HAS_AUTHOR               = 17;  # Target: Simple Account   | Source: Learning object
    const HAS_TUTOR_ENROLMENT      = 18;  # Target: Simple account   | Source: Enrolment
    const HAS_ENQUIRY              = 19;  # Target: Learning object  | Source: Profile
    const HAS_ARCHIVED_ENQUIRY     = 20;  # Target: NULL             | Source: Deleted gc_ro type HAS_ENQUIRY's id - just for handling duplicated archived enquiries
    const HAS_ROLE                 = 500; # Target: Role             | Source: User
    const HAS_ACCOUNT              = 501; # Target: User             | Source: User
    const HAS_TUTOR_EDGE           = 502; # Target: User (Tutor)     | Source: gc_ro id - the record has source_id is course, target_id is (Module)
    const HAS_AUTHOR_EDGE          = 503; # Target: User             | Source: Learning object
    const HAS_MANAGER              = 504; # Target: User (Manager)   | Source: gc_user.id of student
    const HAS_EMAIL                = 505; # Target: gc_user_mail id  | Source: gc_user id
    const HAS_TUTOR_ENROLMENT_EDGE = 506; # Target: gc_enrolment id  | Source: gc_user id
    const HAS_SHARE_WITH           = 507; # Target: Role ID          | Source: Learning object
    const HAS_FOLLOWING            = 508; # Target: gc_user.id       | Source: gc_user.id
    const HAS_PORTAL_EDGE          = 509; # Target: gc_instance.id   | Source: gc_user.id
    const HAS_SHARE_USER_NOTE      = 600; # Target: gc_lo.id         | Source: gc_user.id
    const HAS_SHARE_WITH_LO_USER   = 601; # Target: gc_lo.id         | Source: gc_user.id
    const HAS_MENTION              = 602; # Target: gc_lo.id         | Source: gc_user.id
    const HAS_SHARE_WITH_LO_PORTAL = 603; # Target: gc_instance.id   | Source: Learning object
}
