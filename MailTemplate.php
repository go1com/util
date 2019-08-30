<?php

namespace go1\util;

use go1\util\content_import\ContentImportCompleteCreate;
use InvalidArgumentException;
use ReflectionClass;
use go1\util\queue\Queue;

class MailTemplate
{
    const USER_WELCOME_REGISTER = [
        'key'    => 'user.welcome.register',
        'tokens' => [
            '!user_name'       => 'Machine name of user name, this maybe the email address.', //deprecated
            '@user_name'       => 'User name.', //deprecated
            '!user_first_name' => 'User first name',
            '!site_name'       => 'Human name of the portal.', //deprecated
            '!portal_name'     => 'Portal name',
            '!portal_url'      => 'The portal URL.',
            '!primary_domain'  => 'Primary domain of portal.',
        ],
    ];

    const USER_WELCOME_INVITED = [
        'key'    => 'user.welcome.invite',
        'tokens' => [
            '@user_name'       => 'Name of invited user.', //deprecated
            '!user_name'       => 'Name of invitee user.', //deprecated
            '!user_first_name' => 'User first name.',
            '!user_mail'       => 'User mail.',
            '!host_email'      => 'Email of host user.',
            '!onetime_url'     => 'Onetime login link.',
            '!site_name'       => 'Human name of the portal.', //deprecated
            '!portal_name'     => 'Portal name',
            '!portal_url'      => 'The portal URL.',
            '!primary_domain'  => 'Primary domain of portal.',
        ],
    ];

    const USER_WELCOME_CREATED = [
        'key'    => 'user.welcome.create',
        'tokens' => [
            '!user_name'       => 'Machine name of user name, this maybe the email address.', //deprecated
            '@user_name'       => 'User name.', //deprecated
            '!user_first_name' => 'User first name.',
            '!user_mail'       => 'User mail.',
            '!onetime_url'     => 'Onetime login link.',
            '!site_name'       => 'Human name of the portal.', //deprecated
            '!portal_name'     => 'Portal name',
            '!portal_url'      => 'The portal URL.',
            '!primary_domain'  => 'Primary domain of portal.',
        ],
    ];

    const USER_PASSWORD_FORGET = [
        'key'    => 'user.password.forget',
        'tokens' => [
            '!user_first_name' => 'User first name',
            '!user_mail'       => 'User mail',
            '!primary_domain'  => 'Primary domain of portal',
            '!onetime_url'     => 'Onetime login link',
            '!portal_name'     => 'Portal name',
            '!portal_image'    => 'Portal logo',
            '!portal_url'      => 'Portal URL',
        ],
    ];

    const USER_PASSWORD_CHANGED = [
        'key'    => 'user.password.changed',
        'tokens' => [
            '!user_first_name' => 'User first name',
            '!user_mail'       => 'User mail.',
            '!forget_pw_url'   => 'Link to forget password page.',
            '!portal_name'     => 'Portal name.',
            '!portal_image'    => 'Portal logo.',
            '!portal_url'      => 'Portal URL.',
        ],
    ];

    const USER_EMAIL_CHANGED = [
        'key'    => 'user.email.changed',
        'tokens' => [
            '!user_first_name' => 'User first name',
            '!user_mail'       => 'User mail.',
            '!previous_mail'   => 'Previous user mail',
            '!portal_name'     => 'Portal name.',
            '!portal_image'    => 'Portal logo.',
            '!portal_url'      => 'Portal URL.',
        ],
    ];

    const USER_EMAIL_UPDATED = [
        'key'    => 'user.email.updated',
        'tokens' => [
            '!user_first_name' => 'User first name',
            '!user_mail'       => 'User mail.',
            '!portal_name'     => 'Portal name.',
            '!portal_image'    => 'Portal logo.',
            '!portal_url'      => 'Portal URL.',
        ],
    ];

    const USER_ONETIME_LOGIN = [
        'key'    => 'user.onetime-login',
        'tokens' => [/* @TODO */],
    ];

    const USER_LOGIN_FAILED = [
        'key'    => 'user.login.failed',
        'tokens' => [
            '!user_first_name' => 'User first name',
            '!user_mail'       => 'User mail',
            '!site_name'       => 'Human name of the portal',
            '!primary_domain'  => 'Primary domain of portal',
            '!onetime_url'     => 'Onetime login link',
            '!portal_name'     => 'Portal name',
            '!portal_image'    => 'Portal logo',
            '!portal_url'      => 'Portal URL',
        ],
    ];

    const USER_MAIL_SECONDARY_CONFIRM = [
        'key'    => 'user.mail.secondary.confirm',
        'tokens' => [/* @TODO */],
    ];

    const USER_MAIL_SECONDARY_VERIFIED = [
        'key'    => 'user.mail.secondary.verified',
        'tokens' => [/* @TODO */],
    ];

    const ENQUIRY_SENT = [
        'key'    => 'lo.enquiry.sent',
        'tokens' => [
            '!request_url'    => 'Request URL',
            '!id'             => 'Course ID',
            '!user_name'      => 'User full name',
            '!mail'           => 'User mail',
            '!phone'          => 'User phone',
            '!time'           => 'Enquiry time',
            '!title'          => 'Course title',
            '!portal_name'    => 'Portal name',
            '!portal_image'   => 'Portal image',
            '!recipient_mail' => 'Recipient mail',
        ],
    ];

    const ENQUIRY_ACCEPTED = [
        'key'    => 'lo.enquiry.accepted',
        'tokens' => [
            '!learner_name' => 'Learn name',
            '!course_name'  => 'Course name',
            '!course_url'   => 'Course URL',
            '!portal_name'  => 'Portal name',
            '!portal_image' => 'Portal image',
            '!learner_mail' => 'Learner mail',
        ],
    ];

    /* deprecated */
    const ENQUIRY_REJECED = [
        'key'    => 'lo.enquiry.rejected',
        'tokens' => [
            '!title'     => 'Course title',
            '!first'     => 'User first name',
            '!last'      => 'User last name',
            '!site_name' => 'Human name of the portal',
        ],
    ];

    const ENQUIRY_REJECTED = [
        'key'    => 'lo.enquiry.rejected',
        'tokens' => [
            '!learner_name' => 'Learn name',
            '!course_name'  => 'Course name',
            '!course_url'   => 'Course URL',
            '!portal_name'  => 'Portal name',
            '!portal_image' => 'Portal image',
            '!learner_mail' => 'Learner mail',
        ],
    ];

    const ENQUIRY_NOTIFY_STAFF = [
        'key'    => 'lo.enquiry.notify.staff',
        'tokens' => [/* @TODO */],
    ];

    const LEARNER_ENROLMENT_COURSE = [
        'key'    => 'learner.enrol.course',
        'tokens' => [
            '!learner_first_name' => 'Learner first name',
            '!learner_mail'       => 'Learner mail',
            '!course_name'        => 'Course name',
            '!course_url'         => 'Course URL',
            '!portal_name'        => 'Portal name',
            '!portal_image'       => 'Portal image',
        ],
    ];

    const LEARNER_ASSIGN_COURSE_WITH_DUE_DATE = [
        'key'    => 'learner.assign.course.with-due-date',
        'tokens' => [
            '!entity_type'        => '`course` or `resource`',
            '!entity_type_human'  => '`course` or `learning resource`',
            '!learner_first_name' => 'Learner first name',
            '!learner_mail'       => 'Learner mail',
            '!course_name'        => 'Course name',
            '!course_url'         => 'Course URL',
            '!assigner_full_name' => 'Assigner full name',
            '!scheduled_due_date' => 'Scheduled due date',
            '!portal_name'        => 'Portal name',
            '!portal_image'       => 'Portal image',
        ],
    ];

    const LEARNER_ASSIGN_COURSE_WITHOUT_DUE_DATE = [
        'key'    => 'learner.assign.course.without-due-date',
        'tokens' => [
            '!entity_type'        => '`course` or `resource`',
            '!entity_type_human'  => '`course` or `learning resource`',
            '!learner_first_name' => 'Learner first name',
            '!learner_mail'       => 'Learner mail',
            '!course_name'        => 'Course name',
            '!course_url'         => 'Course URL',
            '!assigner_full_name' => 'Assigner full name',
            '!scheduled_due_date' => 'Scheduled due date',
            '!portal_name'        => 'Portal name',
            '!portal_image'       => 'Portal image',
        ],
    ];

    const LEARNER_ASSIGN_COURSE_THEMSELVES_WITH_DUE_DATE = [
        'key'    => 'learner.assign.course.themselves.with-due-date',
        'tokens' => [
            '!entity_type'        => 'Entity type',
            '!learner_first_name' => 'Learner first name',
            '!learner_mail'       => 'Learner mail',
            '!course_name'        => 'Course name',
            '!course_url'         => 'Course URL',
            '!scheduled_due_date' => 'Scheduled due date',
            '!portal_name'        => 'Portal name',
            '!portal_image'       => 'Portal image',
        ],
    ];

    const LEARNER_ASSIGN_COURSE_THEMSELVES_WITHOUT_DUE_DATE = [
        'key'    => 'learner.assign.course.themselves.without-due-date',
        'tokens' => [
            '!entity_type'        => 'Entity type',
            '!learner_first_name' => 'Learner first name',
            '!learner_mail'       => 'Learner mail',
            '!course_name'        => 'Course name',
            '!course_url'         => 'Course URL',
            '!scheduled_due_date' => 'Scheduled due date',
            '!portal_name'        => 'Portal name',
            '!portal_image'       => 'Portal image',
        ],
    ];

    const LEARNER_ASSIGN_COURSE_THROUGH_GROUP = [
        'key'    => 'learner.assign.course.through.group',
        'tokens' => [
            '!entity_type'        => '`course` or `resource`',
            '!learner_first_name' => 'Learner first name',
            '!learner_mail'       => 'Learner mail',
            '!course_name'        => 'Course name',
            '!course_url'         => 'Course URL',
            '!scheduled_due_date' => 'Scheduled due date',
            '!portal_name'        => 'Portal name',
            '!portal_url'         => 'Portal url',
            '!portal_image'       => 'Portal image',
            '!group_title'        => 'Group title',
            '!group_url'          => 'Group url',
        ],
    ];

    const LEARNER_ENROLMENT_EVENT = [
        'key'    => 'learner.enrol.event',
        'tokens' => [
            '!learner_first_name' => 'Learner first name',
            '!learner_mail'       => 'Learner mail',
            '!course_name'        => 'Course name',
            '!course_url'         => 'Course URL',
            '!event_name'         => 'Event name',
            '!portal_name'        => 'Portal name',
            '!portal_image'       => 'Portal image',
        ],
    ];

    const AUTHOR_ENROLMENT_COURSE = [
        'key'    => 'author.enrol.course',
        'tokens' => [
            '!author_first_name' => 'Author first name',
            '!author_mail'       => 'Author mail',
            '!learner_name'      => 'Learner name',
            '!learner_mail'      => 'Learner mail',
            '!course_name'       => 'Course name',
            '!portal_name'       => 'Portal name',
            '!portal_image'      => 'Portal image',
        ],
    ];

    const USER_ENROL_COURSE = [
        'key'    => 'user.enrol.course',
        'tokens' => [
            '!first_name'       => 'User first name',
            '!course_name'      => 'Course name',
            '!author_name'      => 'Author full name',
            '!rating_url'       => 'Rating URL',
            '!pricing_currency' => 'Course price currency',
            '!pricing_price'    => 'Course price',
            '!pricing_tax'      => 'Course price tax',
            '!course_image'     => 'Course image',
            '!year'             => 'Copyright year',
        ],
    ];

    const USER_MANUAL_PAYMENT_CREATE = [
        'key'    => 'user.manual-payment.create',
        'tokens' => [
            '!recipient_name' => 'Recipient full name',
            '!user_name'      => 'User full name',
            '!course_name'    => 'Course name',
            '!description'    => 'Enrolment description',
            '!accept_url'     => 'Accept payment URL',
            '!reject_url'     => 'Reject payment URL',
        ],
    ];

    const USER_MANUAL_PAYMENT_BULK_CREATE = [
        'key'    => 'user.manual-payment.create',
        'tokens' => [
            '!recipient_name' => 'Recipient full name',
            '!recipient_mail' => 'Recipient mail',
            '!learner_name'   => 'Learner full name',
            '!course_name'    => 'Course name',
            '!quantity'       => 'Credit quantity',
            '!description'    => 'Enrolment description',
            '!credit_type'    => 'Credit type',
            '!accept_url'     => 'Accept payment URL',
            '!reject_url'     => 'Reject payment URL',
            '!portal_name'    => 'Portal name',
            '!portal_image'   => 'Portal image',
        ],
    ];

    const USER_MANUAL_PAYMENT_ACCEPT = [
        'key'    => 'user.manual-payment.accept',
        'tokens' => [
            '!payer_name'   => 'Payer full name',
            '!course_name'  => 'Course name',
            '!course_link'  => 'Course URL',
            '!portal_name'  => 'Portal name',
            '!portal_image' => 'Portal image',
            '!learner_mail' => 'Learner mail',
        ],
    ];

    const USER_MANUAL_PAYMENT_REJECT = [
        'key'    => 'user.manual-payment.reject',
        'tokens' => [
            '!payer_name'   => 'Payer full name',
            '!course_name'  => 'Course name',
            '!course_link'  => 'Course URL',
            '!portal_name'  => 'Portal name',
            '!portal_image' => 'Portal image',
            '!learner_mail' => 'Learner mail',
        ],
    ];

    const USER_GROUP_REQUEST = [
        'key'    => 'user.group.request',
        'tokens' => [
            '!recipient_name' => 'Recipient full name',
            '!user_name'      => 'User full name',
            '!user_mail'      => 'User email',
            '!group_name'     => 'Group name',
            '!accept_url'     => 'Accept payment URL',
            '!reject_url'     => 'Reject payment URL',
            '!portal_name'    => 'Portal name',
            '!portal_image'   => 'Portal image',
            '!portal_url'     => 'The portal URL',
        ],
    ];

    const USER_GROUP_REQUEST_ACCEPT = [
        'key'    => 'user.group.request.accept',
        'tokens' => [
            '!recipient_name'       => 'Recipient full name',
            '!recipient_first_name' => 'Recipient first name',
            '!group_name'           => 'Group name',
            '!group_url'            => 'Group URL',
            '!portal_name'          => 'Portal name',
            '!portal_image'         => 'Portal image',
            '!portal_url'           => 'The portal URL',
        ],
    ];

    const USER_GROUP_REQUEST_REJECT = [
        'key'    => 'user.group.request.reject',
        'tokens' => [
            '!recipient_name'       => 'Recipient full name',
            '!recipient_first_name' => 'Recipient first name',
            '!group_name'           => 'Group name',
            '!group_url'            => 'Group URL',
            '!portal_name'          => 'Portal name',
            '!portal_image'         => 'Portal image',
            '!portal_url'           => 'The portal URL',
        ],
    ];

    const GROUP_ASSIGN = [
        'key'    => 'group.assign',
        'tokens' => [
            '!learner_first_name' => 'Learner first name',
            '!learner_mail'       => 'Learner mail',
            '!group_name'         => 'Group name',
            '!group_url'          => 'Group URL',
            '!portal_name'        => 'Portal name',
            '!portal_url'         => 'Portal URL',
            '!portal_image'       => 'Portal image',
        ],
    ];

    const ONBOARD_WELCOME = [
        'key'    => 'onboard.welcome',
        'tokens' => [
            '!user_first_name' => 'First name',
            '!user_name'       => 'User mail',
            '!site_name'       => 'Human name of the portal.',
            '!portal_url'      => 'The portal URL.',
            '!primary_domain'  => 'Primary domain of portal.',
        ],
    ];

    const ONBOARD_COMPLETE = [
        'key'    => 'onboard.complete',
        'tokens' => [
            '!first_name'   => 'First name',
            '!full_name'    => 'Full name',
            '!user_name'    => 'User mail',
            '!phone'        => 'Phone',
            '!license'      => 'License',
            '!product'      => 'Product',
            '!trial'        => 'Trial',
            '!region'       => 'Region',
            '!portal_name'  => 'Portal name',
            '!portal_url'   => 'Portal URL',
            '!portal_image' => 'Portal image',
        ],
    ];

    const CREDIT_REQUEST = [
        'key'    => 'credit.request',
        'tokens' => [
            '!learner_first_name' => 'Learner first name',
            '!learner_full_name'  => 'Learner full name',
            '!course_name'        => 'Course title',
            '!pricing_price'      => 'Course price',
            '!pricing_currency'   => 'Course price currency',
            '!pricing_tax'        => 'Course price tax',
            '!course_url'         => 'Course URL',
            '!approve_link'       => 'Credit approve URL',
            '!reject_link'        => 'Credit reject URL',
            '!portal_name'        => 'Portal name',
            '!portal_image'       => 'Portal image',
            '!manager_mail'       => 'Manager mail',
        ],
    ];

    const CREDIT_REQUEST_ACCEPT = [
        'key'    => 'credit.request.accept',
        'tokens' => [
            '!learner_first_name' => 'Learner first name',
            '!learner_mail'       => 'Learner mail',
            '!manager_name'       => 'Manager first name',
            '!course_name'        => 'Course name',
            '!course_url'         => 'Course URL',
            '!portal_name'        => 'Portal name',
            '!portal_image'       => 'Portal logo',
        ],
    ];

    const CREDIT_REQUEST_REJECT = [
        'key'    => 'credit.request.reject',
        'tokens' => [
            '!learner_first_name' => 'Learner first name',
            '!learner_mail'       => 'Learner mail',
            '!manager_name'       => 'Manager first name',
            '!course_name'        => 'Course name',
            '!course_url'         => 'Course URL',
            '!portal_name'        => 'Portal name',
            '!portal_image'       => 'Portal logo',
        ],
    ];

    const CREDIT_ASSIGN = [
        'key'    => 'credit.assign',
        'tokens' => [
            '!learner_first_name' => 'Learner first name',
            '!learner_name'       => 'Learner full name',
            '!learner_mail'       => 'Learner mail',
            '!portal_name'        => 'Portal name',
            '!portal_image'       => 'Portal logo',
            '!purchaser_name'     => 'Purchaser full name',
            '!onetime_url'        => 'Onetime URL',
        ],
    ];

    const ASSIGNMENT_SUBMIT = [
        'key'    => 'assignment.submit',
        'tokens' => [
            '!learner_name'    => 'Learner full name',
            '!assignment_name' => 'Assignment name',
            '!module_name'     => 'Module name',
            '!submission_time' => 'Submission time',
            '!assignment_url'  => 'Assignment URL',
            '!portal_name'     => 'Portal name',
            '!portal_image'    => 'Portal image',
        ],
    ];

    const ASSIGNMENT_MARKED = [
        'key'    => 'assignment.marked',
        'tokens' => [
            '!li_title'          => 'Learning item title',
            '!li_submission_url' => 'Learning item submission url',
            '!portal_name'       => 'Portal name',
            '!portal_image'      => 'Portal logo',
            '!leaner_mail'       => 'Learner mail',
        ],
    ];

    const QUIZ_MARKING = [
        'key'    => 'quiz.marking',
        'tokens' => [
            '!learner_full_name' => 'Learner full name',
            '!quiz_title'        => 'Quiz title',
            '!quiz_url'          => 'Quiz URL',
            '!portal_name'       => 'Portal name',
            '!portal_image'      => 'Portal logo',
            '!assessor_mail'     => 'Assessor mail',
        ],
    ];

    const QUIZ_MARKED = [
        'key'    => 'quiz.marked',
        'tokens' => [
            '!li_title' => 'Learning item title',
        ],
    ];

    const NOTE_CREATE = [
        'key'    => 'note.create',
        'tokens' => [
            '!user_first_name' => 'User first name',
            '!entity_type'     => 'Note entity type',
            '!entity_name'     => 'Note entity name',
            '!note_url'        => 'Note URL',
            '!portal_name'     => 'Portal name',
            '!portal_image'    => 'Portal logo',
            '!recipient_mail'  => 'Recipient mail',
        ],
    ];

    const NOTE_MENTION = [
        'key'    => 'note.mention',
        'tokens' => [
            '!entity_type'       => 'Note entity type',
            '!entity_name'       => 'Note entity name',
            '!author_first_name' => 'Author first name',
            '!note_url'          => 'Note URL',
            '!portal_name'       => 'Portal name',
            '!portal_image'      => 'Portal logo',
            '!recipient_mail'    => 'Recipient mail',
        ],
    ];

    const AWARD_UPDATE = [
        'key'    => 'award.update',
        'tokens' => [
            '!learner_first_name' => 'Learner first name',
            '!learner_mail'       => 'Learner mail',
            '!award_name'         => 'Award name',
            '!award_url'          => 'Award URL',
            '!portal_name'        => 'Portal name',
            '!portal_image'       => 'Portal logo',
        ],
    ];

    const AWARD_ASSIGN = [
        'key'    => 'award.assign',
        'tokens' => [
            '!learner_first_name' => 'Learner first name',
            '!learner_mail'       => 'Learner mail',
            '!award_name'         => 'Award name',
            '!award_url'          => 'Award URL',
            '!assigner_full_name' => 'Assigner full name',
            '!scheduled_due_date' => 'Scheduled due date',
            '!portal_name'        => 'Portal name',
            '!portal_image'       => 'Portal logo',
        ],
    ];

    const AWARD_ASSIGN_THROUGH_GROUP = [
        'key'    => 'award.assign.through.group',
        'tokens' => [
            '!learner_first_name' => 'Learner first name',
            '!learner_mail'       => 'Learner mail',
            '!award_name'         => 'Award name',
            '!award_url'          => 'Award URL',
            '!assigner_full_name' => 'Assigner full name',
            '!scheduled_due_date' => 'Scheduled due date',
            '!portal_name'        => 'Portal name',
            '!portal_image'       => 'Portal logo',
            '!group_title'        => 'Group title',
            '!group_url'          => 'Group url',
        ],
    ];

    const AWARD_ACHIEVE = [
        'key'    => 'award.achieve',
        'tokens' => [
            '!learner_first_name' => 'Learner first name',
            '!learner_mail'       => 'Learner mail',
            '!award_name'         => 'Award name',
            '!award_url'          => 'Award URL',
            '!award_expiry'       => 'Award expiry date',
            '!portal_name'        => 'Portal name',
            '!portal_image'       => 'Portal logo',
        ],
    ];

    const AWARD_LIST = [
        'key'    => 'award.list',
        'tokens' => [
            '!award_name'    => 'Award name',
            '!award_image'   => 'Award image',
            '!award_url'     => 'Award URL',
            '!award_content' => 'Award html content',
            '!portal_name'   => 'Portal name',
            '!portal_image'  => 'Portal logo',
            '!portal_url'    => 'Portal URL',
        ],
    ];

    const COURSE_COMPLETE = [
        'key'    => 'course.complete',
        'tokens' => [
            '!user_first_name' => 'User first name',
            '!user_mail'       => 'User mail',
            '!course_name'     => 'Course name',
            '!portal_url'      => 'Portal URL',
        ],
    ];

    // No activity after registration (user)
    const REMIND_NO_ACTIVITY_USER = [
        'key'    => 'user.remind.no-activity-user',
        'tokens' => [
            '!user_first_name' => 'User first name',
            '!user_mail'       => 'User mail',
            '!portal_name'     => 'Portal name',
            '!portal_url'      => 'Portal URL',
            '!portal_image'    => 'Portal logo',
        ],
    ];

    // No activity after registration (collab)
    const REMIND_NO_ACTIVITY_COLLABORATOR = [
        'key'    => 'user.remind.no-activity-collaborator',
        'tokens' => [
            '!user_first_name' => 'User first name',
            '!user_mail'       => 'User mail',
            '!portal_name'     => 'Portal name',
            '!portal_url'      => 'Portal URL',
            '!portal_image'    => 'Portal logo',
        ],
    ];

    // Expiry of free access
    const REMIND_FREE_TRIAL_EXPIRE = [
        'key'    => 'user.remind.free-trial-expire',
        'tokens' => [
            '!user_first_name' => 'User first name',
            '!user_mail'       => 'User mail',
            '!portal_name'     => 'Portal name',
            '!portal_url'      => 'Portal URL',
            '!portal_image'    => 'Portal logo',
        ],
    ];

    // Free access expired encouragement
    const REMIND_FREE_TRIAL_EXPIRED = [
        'key'    => 'user.remind.free-trial-expired',
        'tokens' => [
            '!user_first_name' => 'User first name',
            '!user_mail'       => 'User mail',
            '!portal_name'     => 'Portal name',
            '!portal_url'      => 'Portal URL',
            '!portal_image'    => 'Portal logo',
        ],
    ];

    // Remind to start course
    const REMIND_COURSE = [
        'key'    => 'user.remind.course',
        'tokens' => [
            '!user_first_name' => 'User first name',
            '!user_mail'       => 'User mail',
            '!course_name'     => 'Course name',
            '!course_url'      => 'Course URL',
            '!portal_name'     => 'Portal name',
            '!portal_url'      => 'Portal URL',
            '!portal_image'    => 'Portal logo',
        ],
    ];

    // Expiry of subscription
    const REMIND_SUBSCRIPTION_EXPIRE = [
        'key'    => 'user.remind.subscription-expire',
        'tokens' => [
            '!user_first_name' => 'User first name',
            '!user_mail'       => 'User mail',
            '!portal_name'     => 'Portal name',
            '!portal_url'      => 'Portal URL',
            '!portal_image'    => 'Portal logo',
        ],
    ];

    // Subscription expired
    const REMIND_SUBSCRIPTION_EXPIRED = [
        'key'    => 'user.remind.subscription-expired',
        'tokens' => [
            '!user_first_name' => 'User first name',
            '!user_mail'       => 'User mail',
            '!portal_name'     => 'Portal name',
            '!portal_url'      => 'Portal URL',
            '!portal_image'    => 'Portal logo',
        ],
    ];

    // Contact us enquiry form
    const CONTACT_US_ENQUIRY = [
        'key'    => 'user.contact-us-enquiry',
        'tokens' => [
            '!user_first_name' => 'User first name',
            '!user_mail'       => 'User mail',
            '!user_phone'      => 'User phone',
            '!user_message'    => 'User message',
        ],
    ];

    // Decision and Action started
    const DECISION_AND_ACTION_START = [
        'key'    => 'bos.decision.start',
        'tokens' => [
            '!user_first_name'   => 'User first name',
            '!user_mail'         => 'User mail',
            '!project_name'      => 'Project name',
            '!collaborator_name' => 'Collaborator name',
        ],
    ];

    // Decision and Action ended
    const DECISION_AND_ACTION_END = [
        'key'    => 'bos.decision.end',
        'tokens' => [
            '!user_first_name'   => 'User first name',
            '!user_mail'         => 'User mail',
            '!project_name'      => 'Project name',
            '!collaborator_name' => 'Collaborator name',
        ],
    ];

    const MARKETPLACE_REQUEST = [
        'key'    => 'marketplace.request',
        'tokens' => [
            '!manager_name'   => 'Manager name',
            '!manager_mail'   => 'Manager mail',
            '!course_name'    => 'Course name',
            '!course_url'     => 'Course URL',
            '!setting_url'    => 'Setting URL',
            '!portal_name'    => 'Portal name',
            '!portal_image'   => 'Portal logo',
            '!portal_url'     => 'Portal URL',
            '!recipient_mail' => 'Recipient mail',
        ],
    ];

    const MARKETPLACE_APPROVE = [
        'key'    => 'marketplace.approve',
        'tokens' => [
            '!staff_first_name' => 'User first name',
            '!manager_name'     => 'Manager name',
            '!course_name'      => 'Course name',
            '!portal_name'      => 'Portal name',
            '!portal_image'     => 'Portal logo',
            '!manager_mail'     => 'Manager mail',
        ],
    ];

    const MARKETPLACE_REJECT = [
        'key'    => 'marketplace.reject',
        'tokens' => [
            '!staff_first_name' => 'User first name',
            '!manager_name'     => 'Manager name',
            '!course_name'      => 'Course name',
            '!portal_name'      => 'Portal name',
            '!portal_image'     => 'Portal logo',
            '!manager_mail'     => 'Manager mail',
        ],
    ];

    const PAYMENT_STRIPE_AUTHORIZE = [
        'key'    => 'payment.stripe.authorize',
        'tokens' => [
            '!manager_name' => 'Manager name',
            '!portal_name'  => 'Portal name',
            '!portal_image' => 'Portal logo',
            '!portal_url'   => 'Portal URL',
            '!manager_mail' => 'Manager mail',
        ],
    ];

    const PAYMENT_STRIPE_DEAUTHORIZE = [
        'key'    => 'payment.stripe.deauthorize',
        'tokens' => [
            '!manager_name' => 'Manager name',
            '!portal_name'  => 'Portal name',
            '!portal_image' => 'Portal logo',
            '!portal_url'   => 'Portal URL',
            '!manager_mail' => 'Manager mail',
        ],
    ];

    const PORTAL_CONFIG = [
        'key'    => 'portal.config',
        'tokens' => [
            '!user_full_name'  => 'User full name',
            '!user_mail'       => 'User mail',
            '!current_product' => 'Current product',
            '!current_license' => 'Current license',
            '!current_price'   => 'Current price',
            '!current_status'  => 'Current status',
            '!expire_date'     => 'Expire date',
            '!new_product'     => 'New product',
            '!new_license'     => 'New license',
            '!portal_name'     => 'Portal name',
            '!portal_image'    => 'Portal logo',
            '!portal_url'      => 'Portal URL',
            '!portal_title'    => 'Portal title',
            '!recipient_mail'  => 'Recipient mail',
        ],
    ];

    const REMIND_LEARNER_DUE_DATE_REACHED = [
        'key'    => 'remind.learner.due-date-reached',
        'tokens' => [
            '!time_remaining'        => 'Time remaining',
            '!learning_object_title' => 'Learning object title',
            '!learner_first_name'    => 'Learner first name',
            '!assigner_full_name'    => 'Assigner full name',
            '!scheduled_due_date'    => 'Scheduled due date',
            '!learner_mail'          => 'Learner mail',
            '!action_url'            => 'Action url',
            '!portal_name'           => 'Portal name',
            '!portal_image'          => 'Portal image',
            '!portal_url'            => 'Portal URL',
        ],
    ];

    const REMIND_LEARNER_75_PERCENT_SCHEDULED_DURATION_PASSED = [
        'key'    => 'remind.learner.75percent-scheduled-duration-passed',
        'tokens' => [
            '!time_remaining'        => 'Time remaining',
            '!learning_object_title' => 'Learning object title',
            '!learner_first_name'    => 'Learner first name',
            '!assigner_full_name'    => 'Assigner full name',
            '!scheduled_due_date'    => 'Scheduled due date',
            '!learner_mail'          => 'Learner mail',
            '!action_url'            => 'Action url',
            '!portal_name'           => 'Portal name',
            '!portal_image'          => 'Portal image',
            '!portal_url'            => 'Portal URL',
        ],
    ];

    const REMIND_LEARNER_DUE_DATE_2_DAYS_OVERDUE = [
        'key'    => 'remind.learner.due-date-2-days-overdue',
        'tokens' => [
            '!time_remaining'        => 'Time remaining',
            '!learning_object_title' => 'Learning object title',
            '!learner_first_name'    => 'Learner first name',
            '!assigner_full_name'    => 'Assigner full name',
            '!scheduled_due_date'    => 'Scheduled due date',
            '!learner_mail'          => 'Learner mail',
            '!action_url'            => 'Action url',
            '!portal_name'           => 'Portal name',
            '!portal_image'          => 'Portal image',
            '!portal_url'            => 'Portal URL',
        ],
    ];

    const REMIND_LEARNER_EVENT_ON_START_DATE = [
        'key'    => 'remind.learner.event-on-start-date',
        'tokens' => [
            '!event_session_content' => 'Event sessions',
            '!event_name'            => 'Event name',
            '!event_start_date'      => 'Event start date',
            '!event_start_time'      => 'Event start time',
            '!event_end_date'        => 'Event end date',
            '!event_end_time'        => 'Event end time',
            '!event_address'         => 'Event address',
            '!learner_first_name'    => 'Learner first name',
            '!course_name'           => 'Course name',
            '!course_url'            => 'Course URL',
            '!learner_mail'          => 'Learner mail',
            '!view_detail_url'       => 'View detail url',
            '!portal_name'           => 'Portal name',
            '!portal_image'          => 'Portal image',
            '!portal_url'            => 'Portal URL',
        ],
    ];

    const REMIND_LEARNER_EVENT_UPCOMING = [
        'key'    => 'remind.learner.event-upcoming',
        'tokens' => [
            '!event_session_content' => 'Event sessions',
            '!event_name'            => 'Event name',
            '!event_start_date'      => 'Event start date',
            '!event_start_time'      => 'Event start time',
            '!event_end_date'        => 'Event end date',
            '!event_end_time'        => 'Event end time',
            '!event_address'         => 'Event address',
            '!learner_first_name'    => 'Learner first name',
            '!course_name'           => 'Course name',
            '!course_url'            => 'Course URL',
            '!learner_mail'          => 'Learner mail',
            '!view_detail_url'       => 'View detail url',
            '!portal_name'           => 'Portal name',
            '!portal_image'          => 'Portal image',
            '!portal_url'            => 'Portal URL',
        ],
    ];

    const REMIND_ASSESSOR_EVENT_ON_START_DATE = [
        'key'    => 'remind.assessor.event-on-start-date',
        'tokens' => [
            '!event_name'          => 'Event name',
            '!event_start_date'    => 'Event start date',
            '!event_start_time'    => 'Event start time',
            '!event_end_date'      => 'Event end date',
            '!event_end_time'      => 'Event end time',
            '!event_address'       => 'Event address',
            '!number_of_attendees' => 'Number of attendees',
            '!assessor_first_name' => 'Assessor first name',
            '!course_name'         => 'Course name',
            '!course_url'          => 'Course URL',
            '!assessor_mail'       => 'Assessor mail',
            '!view_attendees_url'  => 'View attendees url',
            '!portal_name'         => 'Portal name',
            '!portal_image'        => 'Portal image',
            '!portal_url'          => 'Portal URL',
        ],
    ];

    const REMIND_ASSESSOR_EVENT_UPCOMING = [
        'key'    => 'remind.assessor.event-upcoming',
        'tokens' => [
            '!event_name'          => 'Event name',
            '!event_start_date'    => 'Event start date',
            '!event_start_time'    => 'Event start time',
            '!event_end_date'      => 'Event end date',
            '!event_end_time'      => 'Event end time',
            '!event_address'       => 'Event address',
            '!number_of_attendees' => 'Number of attendees',
            '!assessor_first_name' => 'Assessor first name',
            '!course_name'         => 'Course name',
            '!course_url'          => 'Course URL',
            '!assessor_mail'       => 'Assessor mail',
            '!view_attendees_url'  => 'View attendees url',
            '!portal_name'         => 'Portal name',
            '!portal_image'        => 'Portal image',
            '!portal_url'          => 'Portal URL',
        ],
    ];

    const REMIND_LEARNER_AWARD_EXPIRY_DATE_REACHED = [
        'key'    => 'remind.learner.award-expiry-date-reached',
        'tokens' => [
            '!award_title'        => 'Award title',
            '!expiry_date'        => 'Expiry date',
            '!award_url'          => 'Award URL',
            '!learner_mail'       => 'Learner mail',
            '!learner_first_name' => 'Learner first name',
            '!portal_name'        => 'Portal name',
            '!portal_image'       => 'Portal image',
            '!portal_url'         => 'Portal URL',
        ],
    ];

    const REMIND_LEARNER_AWARD_EXPIRY_2DAYS_OVERDUE = [
        'key'    => 'remind.learner.award-expiry-2days-overdue',
        'tokens' => [
            '!award_title'        => 'Award title',
            '!expiry_date'        => 'Expiry date',
            '!award_url'          => 'Award URL',
            '!learner_mail'       => 'Learner mail',
            '!learner_first_name' => 'Learner first name',
            '!portal_name'        => 'Portal name',
            '!portal_image'       => 'Portal image',
            '!portal_url'         => 'Portal URL',
        ],
    ];

    const REMIND_LEARNER_AWARD_75_PERCENT_COMPLETED_DURATION_PASSED = [
        'key'    => 'remind.learner.award-75percent-completed-duration-passed',
        'tokens' => [
            '!award_title'        => 'Award title',
            '!expiry_date'        => 'Expiry date',
            '!award_url'          => 'Award URL',
            '!learner_mail'       => 'Learner mail',
            '!learner_first_name' => 'Learner first name',
            '!portal_name'        => 'Portal name',
            '!portal_image'       => 'Portal image',
            '!portal_url'         => 'Portal URL',
            '!time_remaining'     => 'Time remaining',
        ],
    ];

    const REMIND_MANAGER_AWARD_EXPIRY_DATE_REACHED = [
        'key'    => 'remind.manager.award-expiry-date-reached',
        'tokens' => [
            '!award_title'        => 'Award title',
            '!award_url'          => 'Award URL',
            '!expiry_date'        => 'Expiry date',
            '!learner_mail'       => 'Learner mail',
            '!learner_first_name' => 'Learner first name',
            '!learner_full_name'  => 'Learner full name',
            '!manager_mail'       => 'Manager mail',
            '!manager_first_name' => 'Manager first name',
            '!portal_name'        => 'Portal name',
            '!portal_image'       => 'Portal image',
            '!portal_url'         => 'Portal URL',
        ],
    ];

    const REMIND_MANAGER_AWARD_EXPIRY_2DAYS_OVERDUE = [
        'key'    => 'remind.manager.award-expiry-2days-overdue',
        'tokens' => [
            '!award_title'        => 'Award title',
            '!award_url'          => 'Award URL',
            '!expiry_date'        => 'Expiry date',
            '!learner_mail'       => 'Learner mail',
            '!learner_first_name' => 'Learner first name',
            '!learner_full_name'  => 'Learner full name',
            '!manager_mail'       => 'Manager mail',
            '!manager_first_name' => 'Manager first name',
            '!portal_name'        => 'Portal name',
            '!portal_image'       => 'Portal image',
            '!portal_url'         => 'Portal URL',
        ],
    ];

    const REMIND_MANAGER_AWARD_75_PERCENT_COMPLETED_DURATION_PASSED = [
        'key'    => 'remind.manager.award-75percent-completed-duration-passed',
        'tokens' => [
            '!award_title'        => 'Award title',
            '!award_url'          => 'Award URL',
            '!expiry_date'        => 'Expiry date',
            '!learner_mail'       => 'Learner mail',
            '!learner_first_name' => 'Learner first name',
            '!learner_full_name'  => 'Learner full name',
            '!manager_mail'       => 'Manager mail',
            '!manager_first_name' => 'Manager first name',
            '!portal_name'        => 'Portal name',
            '!portal_image'       => 'Portal image',
            '!portal_url'         => 'Portal URL',
        ],
    ];

    const REMIND_ASSESSOR_AWARD_EXPIRY_DATE_REACHED = [
        'key'    => 'remind.assessor.award-expiry-date-reached',
        'tokens' => [
            '!assessor_first_name' => 'Assessor first name',
            '!learner_full_name'   => 'Learner full name',
            '!award_title'         => 'Award title',
            '!award_url'           => 'Award URL',
            '!expiry_date'         => 'Expiry date',
            '!assessor_mail'       => 'Assessor mail',
            '!portal_name'         => 'Portal name',
            '!portal_image'        => 'Portal image',
            '!portal_url'          => 'Portal URL',
        ],
    ];

    const REMIND_ASSESSOR_75_PERCENT_SCHEDULED_DURATION_PASSED = [
        'key'    => 'remind.assessor.75percent-scheduled-duration-passed',
        'tokens' => [
            '!learning_object_title' => 'Learning object title',
            '!assessor_first_name'   => 'Assessor first name',
            '!learner_full_name'     => 'Learner full name',
            '!scheduled_due_date'    => 'Scheduled due date',
            '!time_remaining'        => 'Time remaining',
            '!assessor_email'        => 'Assessor email',
            '!action_url'            => 'Action url',
            '!portal_name'           => 'Portal name',
            '!portal_image'          => 'Portal image',
            '!portal_url'            => 'Portal URL',
        ],
    ];

    const REMIND_ASSESSOR_DUE_DATE_2_DAYS_OVERDUE = [
        'key'    => 'remind.assessor.due-date-2-days-overdue',
        'tokens' => [
            '!learning_object_title' => 'Learning object title',
            '!assessor_first_name'   => 'Assessor first name',
            '!learner_full_name'     => 'Learner full name',
            '!scheduled_due_date'    => 'Scheduled due date',
            '!assessor_email'        => 'Assessor email',
            '!action_url'            => 'Action url',
            '!portal_name'           => 'Portal name',
            '!portal_image'          => 'Portal image',
            '!portal_url'            => 'Portal URL',
        ],
    ];

    const REMIND_ASSESSOR_DUE_DATE_REACHED = [
        'key'    => 'remind.assessor.due-date-reached',
        'tokens' => [
            '!learning_object_title' => 'Learning object title',
            '!assessor_first_name'   => 'Assessor first name',
            '!learner_full_name'     => 'Learner full name',
            '!scheduled_due_date'    => 'Scheduled due date',
            '!assessor_email'        => 'Assessor email',
            '!action_url'            => 'Action url',
            '!portal_name'           => 'Portal name',
            '!portal_image'          => 'Portal image',
            '!portal_url'            => 'Portal URL',
        ],
    ];

    const REMIND_ASSESSOR_AWARD_EXPIRY_2DAYS_OVERDUE = [
        'key'    => 'remind.assessor.award-expiry-2days-overdue',
        'tokens' => [
            '!assessor_first_name' => 'Assessor first name',
            '!learner_full_name'   => 'Learner full name',
            '!award_title'         => 'Award title',
            '!award_url'           => 'Award URL',
            '!expiry_date'         => 'Expiry date',
            '!assessor_mail'       => 'Assessor mail',
            '!portal_name'         => 'Portal name',
            '!portal_image'        => 'Portal image',
            '!portal_url'          => 'Portal URL',
        ],
    ];

    const REMIND_ASSESSOR_AWARD_75_PERCENT_COMPLETED_DURATION_PASSED = [
        'key'    => 'remind.assessor.award-75percent-completed-duration-passed',
        'tokens' => [
            '!assessor_first_name' => 'Assessor first name',
            '!learner_full_name'   => 'Learner full name',
            '!award_title'         => 'Award title',
            '!award_url'           => 'Award URL',
            '!expiry_date'         => 'Expiry date',
            '!assessor_mail'       => 'Assessor mail',
            '!portal_name'         => 'Portal name',
            '!portal_image'        => 'Portal image',
            '!portal_url'          => 'Portal URL',
        ],
    ];

    const LEARNER_AWARD_ITEM_MANUAL_IS_ADDED = [
        'key'    => 'learner.award.item-manual.is-added',
        'tokens' => [
            '!submitter_full_name' => 'Submitter full name',
            '!learner_first_name'  => 'Learner first name',
            '!learner_mail'        => 'Learner mail',
            '!award_title'         => 'Award title',
            '!award_url'           => 'Award URL',
            '!award_records_url'   => 'Award records URL',
            '!award_item_title'    => 'Award item title',
            '!submitter_mail'      => 'Submitter mail',
            '!portal_name'         => 'Portal name',
            '!portal_image'        => 'Portal image',
            '!portal_url'          => 'Portal URL',
        ],
    ];

    const LEARNER_AWARD_ITEM_MANUAL_IS_MODIFIED = [
        'key'    => 'learner.award.item-manual.is-modified',
        'tokens' => [
            '!submitter_full_name' => 'Submitter full name',
            '!learner_first_name'  => 'Learner first name',
            '!learner_mail'        => 'Learner mail',
            '!award_title'         => 'Award title',
            '!award_url'           => 'Award URL',
            '!award_records_url'   => 'Award records URL',
            '!award_item_title'    => 'Award item title',
            '!submitter_mail'      => 'Submitter mail',
            '!portal_name'         => 'Portal name',
            '!portal_image'        => 'Portal image',
            '!portal_url'          => 'Portal URL',
        ],
    ];

    const LEARNER_AWARD_ITEM_MANUAL_IS_PASSED = [
        'key'    => 'learner.award.item-manual.is-passed',
        'tokens' => [
            '!submitter_full_name' => 'Submitter full name',
            '!learner_first_name'  => 'Learner first name',
            '!learner_mail'        => 'Learner mail',
            '!award_title'         => 'Award title',
            '!award_url'           => 'Award URL',
            '!award_records_url'   => 'Award records URL',
            '!award_item_title'    => 'Award item title',
            '!submitter_mail'      => 'Submitter mail',
            '!portal_name'         => 'Portal name',
            '!portal_image'        => 'Portal image',
            '!portal_url'          => 'Portal URL',
        ],
    ];

    const ASSESSOR_AWARD_ITEM_MANUAL_IS_ADDED = [
        'key'    => 'assessor.award.item-manual.is-added',
        'tokens' => [
            '!editor_full_name'    => 'Editor full name',
            '!assessor_first_name' => 'Assessor first name',
            '!assessor_mail'       => 'Assessor mail',
            '!learner_full_name'   => 'Learner full name',
            '!learner_first_name'  => 'Learner first name',
            '!learner_mail'        => 'Learner mail',
            '!award_title'         => 'Award title',
            '!award_url'           => 'Award Url',
            '!award_records_url'   => 'Award records URL',
            '!award_item_title'    => 'Award item title',
            '!award_enrolment_url' => 'Award enrolment URL',
            '!portal_name'         => 'Portal name',
            '!portal_image'        => 'Portal image',
            '!portal_url'          => 'Portal URL',
        ],
    ];

    const ASSESSOR_AWARD_ITEM_MANUAL_IS_MODIFIED = [
        'key'    => 'assessor.award.item-manual.is-modified',
        'tokens' => [
            '!editor_full_name'    => 'Editor full name',
            '!assessor_first_name' => 'Assessor first name',
            '!assessor_mail'       => 'Assessor mail',
            '!learner_full_name'   => 'Learner full name',
            '!learner_first_name'  => 'Learner first name',
            '!learner_mail'        => 'Learner mail',
            '!award_title'         => 'Award title',
            '!award_url'           => 'Award Url',
            '!award_records_url'   => 'Award records URL',
            '!award_item_title'    => 'Award item title',
            '!award_enrolment_url' => 'Award enrolment URL',
            '!portal_name'         => 'Portal name',
            '!portal_image'        => 'Portal image',
            '!portal_url'          => 'Portal URL',
        ],
    ];

    const REMIND_MANAGER_75_PERCENT_SCHEDULED_DURATION_PASSED = [
        'key'    => 'remind.manager.75percent-scheduled-duration-passed',
        'tokens' => [
            '!learning_object_title' => 'Learning object title',
            '!manager_first_name'    => 'Manager first name',
            '!learner_full_name'     => 'Learner full name',
            '!scheduled_due_date'    => 'Scheduled due date',
            '!time_remaining'        => 'Time remaining',
            '!manager_email'         => 'Manager email',
            '!action_url'            => 'Action URL',
            '!portal_name'           => 'Portal name',
            '!portal_image'          => 'Portal image',
            '!portal_url'            => 'Portal URL',
        ],
    ];

    const REMIND_MANAGER_DUE_DATE_2_DAYS_OVERDUE = [
        'key'    => 'remind.manager.due-date-2-days-overdue',
        'tokens' => [
            '!learning_object_title' => 'Learning object title',
            '!assessor_first_name'   => 'Manager first name',
            '!learner_full_name'     => 'Learner full name',
            '!scheduled_due_date'    => 'Scheduled due date',
            '!manager_email'         => 'Manager email',
            '!action_url'            => 'Action URL',
            '!portal_name'           => 'Portal name',
            '!portal_image'          => 'Portal image',
            '!portal_url'            => 'Portal URL',
        ],
    ];

    const REMIND_MANAGER_DUE_DATE_REACHED = [
        'key'    => 'remind.manager.due-date-reached',
        'tokens' => [
            '!learning_object_title' => 'Learning object title',
            '!manager_first_name'    => 'Manager first name',
            '!learner_full_name'     => 'Learner full name',
            '!scheduled_due_date'    => 'Scheduled due date',
            '!manager_email'         => 'Manager email',
            '!action_url'            => 'Action URL',
            '!portal_name'           => 'Portal name',
            '!portal_image'          => 'Portal image',
            '!portal_url'            => 'Portal URL',
        ],
    ];

    const ENROLMENT_REPORT = [
        'key'    => 'enrolment.report',
        'tokens' => [
            '!recipient_mail' => 'Recipient mail',
            '!company_name'   => 'Company name',
            '!portal_name'    => 'Portal name',
            '!portal_image'   => 'Portal image',
            '!portal_url'     => 'Portal URL',
        ],
    ];

    const AWARD_ASSIGN_THEMSELVES = [
        'key'    => 'award.assign.themselves',
        'tokens' => [
            '!learner_first_name' => 'Learner first name',
            '!learner_mail'       => 'Learner mail',
            '!award_name'         => 'Award name',
            '!award_url'          => 'Award URL',
            '!scheduled_due_date' => 'Scheduled due date',
            '!portal_name'        => 'Portal name',
            '!portal_image'       => 'Portal logo',
        ],
    ];

    const ASSESSOR_ASSIGNMENT_FEEDBACK = [
        'key'    => 'assessor.assignment.feedback',
        'tokens' => [
            '!learner_full_name' => 'Learner full name',
            '!learner_mail'      => 'Learner mail',
            '!recipient_mail'    => 'Recipient mail',
            '!assignment_name'   => 'Assignment name',
            '!assignment_url'    => 'Assignment URL',
            '!my_teaching_url'   => 'My teaching URL',
            '!description'       => 'Feedback of an assignment',
            '!my_reply_url'      => 'My reply URL',
            '!portal_name'       => 'Portal name',
            '!portal_image'      => 'Portal image',
            '!portal_url'        => 'Portal URL',
        ],
    ];

    const LEARNER_ASSIGNMENT_FEEDBACK = [
        'key'    => 'learner.assignment.feedback',
        'tokens' => [
            '!assessor_full_name' => 'Assessor full name',
            '!learner_mail'       => 'Learner mail',
            '!assignment_name'    => 'Assignment name',
            '!assignment_url'     => 'Assignment URL',
            '!description'        => 'Feedback of an assignment',
            '!my_reply_url'       => 'My reply URL',
            '!portal_name'        => 'Portal name',
            '!portal_image'       => 'Portal image',
            '!portal_url'         => 'Portal URL',
        ],
    ];

    public const USER_BULK_NOTIFY = [
        'key'    => Queue::USER_BULK_NOTIFY,
        'tokens' => [
            '@user_name'       => 'User name.', //deprecated
            '!user_name'       => 'Machine name of user name, this maybe the email address.', //deprecated
            '!site_name'       => 'Human name of the portal.', //deprecated
            '!portal_url'      => 'Portal URL',
            '!primary_domain'  => 'Primary domain of portal.',
            '!onetime_url'     => 'Onetime login link.',
        ]
    ];

    const LEARNER_RECOMMENDATION_FORTNIGHTLY = [
        'key'    => 'learner.recommendation.fortnightly',
        'tokens' => [/* @TODO */],
    ];

    public const CONTENT_IMPORT_COMPLETE = [
        'key'    => ContentImportCompleteCreate::ROUTING_KEY,
        'tokens' => [
            '!user_first_name'   => 'User first name',
            '!content_import_status' => 'Content job import status',
            '!processed_count' => 'Total processed count, which is successCount + failedCount',
            '!success_count' => 'Success Count',
            '!failed_count' => 'Failed count',
            '!portal' => "Portal name",
            '!user_mail' => 'User email',
        ]
    ];


    public static function has(string $key): bool
    {
        $self = new ReflectionClass(__CLASS__);
        foreach ($self->getConstants() as $constant => $value) {
            if (isset($value['key'])) {
                if ($key === $value['key']) {
                    return true;
                }
            }
        }

        return false;
    }

    public static function token(string $key)
    {
        $self = new ReflectionClass(__CLASS__);
        foreach ($self->getConstants() as $constant => $value) {
            if (isset($value['key'])) {
                if ($key === $value['key']) {
                    return $value['tokens'];
                }
            }
        }

        return new InvalidArgumentException('Mail template not found.');
    }
}
