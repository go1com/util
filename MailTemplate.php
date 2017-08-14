<?php

namespace go1\util;

use InvalidArgumentException;
use ReflectionClass;

class MailTemplate
{
    const USER_WELCOME_REGISTER = [
        'key'    => 'user.welcome.register',
        'tokens' => [
            '!user_name'      => 'Machine name of user name, this maybe the email address.',
            '@user_name'      => 'User name.',
            '!site_name'      => 'Human name of the portal.',
            '!portal_url'     => 'The portal URL.',
            '!primary_domain' => 'Primary domain of portal.',
        ],
    ];

    const USER_WELCOME_INVITED = [
        'key'    => 'user.welcome.invite',
        'tokens' => [
            '@user_name'      => 'Name of invited user.',
            '!user_name'      => 'Name of invitee user.',
            '!host_email'     => 'Email of host user.',
            '!onetime_url'    => 'Onetime login link.',
            '!site_name'      => 'Human name of the portal.',
            '!portal_url'     => 'The portal URL.',
            '!primary_domain' => 'Primary domain of portal.',
        ],
    ];

    const USER_WELCOME_CREATED = [
        'key'    => 'user.welcome.create',
        'tokens' => [
            '!user_name'      => 'Machine name of user name, this maybe the email address.',
            '@user_name'      => 'User name.',
            '!onetime_url'    => 'Onetime login link.',
            '!site_name'      => 'Human name of the portal.',
            '!portal_url'     => 'The portal URL.',
            '!primary_domain' => 'Primary domain of portal.',
        ],
    ];

    const USER_PASSWORD_FORGET = [
        'key'    => 'user.password.forget',
        'tokens' => [
            '@user_name'   => 'User name',
            '!site_name'   => 'Human name of the portal.',
            '!portal_url'  => 'The portal URL.',
            '!onetime_url' => 'Onetime login link.',
        ],
    ];

    const USER_PASSWORD_CHANGED = [
        'key'    => 'user.password.changed',
        'tokens' => [
            '@user_name'     => 'Name of user.',
            '!site_name'     => 'Human name of the portal',
            '!portal_url'    => 'The portal URL.',
            '!forget_pw_url' => 'Link to forget password page.',
        ],
    ];

    const USER_ONETIME_LOGIN = [
        'key'    => 'user.onetime-login',
        'tokens' => [/* @TODO */],
    ];

    const USER_LOGIN_FAIL = [
        'key'    => 'user.onetime-login',
        'tokens' => [
            '@user_name'   => 'Name of user.',
            '!user_name'   => 'User mail',
            '!site_name'   => 'Human name of the portal',
            '!portal_url'  => 'The portal URL.',
            '!onetime_url' => 'Onetime login link.',
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

    const LEARNER_ASSIGN_COURSE = [
        'key'    => 'learner.assign.course',
        'tokens' => [
            '!learner_first_name' => 'Learner first name',
            '!learner_mail'       => 'Learner mail',
            '!course_name'        => 'Course name',
            '!course_url'         => 'Course URL',
            '!portal_name'        => 'Portal name',
            '!portal_image'       => 'Portal image',
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
            '!author_first_name'  => 'Author first name',
            '!author_mail'        => 'Author mail',
            '!learner_first_name' => 'Learner first name',
            '!course_name'        => 'Course name',
            '!portal_name'        => 'Portal name',
            '!portal_image'       => 'Portal image',
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
            '!group_title'    => 'Group name',
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
            '!recipient_name' => 'Recipient full name',
            '!group_title'    => 'Group name',
            '!portal_name'    => 'Portal name',
            '!portal_image'   => 'Portal image',
            '!portal_url'     => 'The portal URL',
        ],
    ];

    const USER_GROUP_REQUEST_REJECT = [
        'key'    => 'user.group.request.reject',
        'tokens' => [
            '!recipient_name' => 'Recipient full name',
            '!group_title'    => 'Group name',
            '!portal_name'    => 'Portal name',
            '!portal_image'   => 'Portal image',
            '!portal_url'     => 'The portal URL',
        ],
    ];

    const ONBOARD_WELCOME = [
        'key'    => 'onboard.welcome',
        'tokens' => [
            '!first_name'     => 'First name',
            '!user_name'      => 'User mail',
            '!site_name'      => 'Human name of the portal.',
            '!portal_url'     => 'The portal URL.',
            '!primary_domain' => 'Primary domain of portal.',
        ],
    ];

    const ONBOARD_SENT = [
        'key'    => 'onboard.sent',
        'tokens' => [
            '!full_name'  => 'User full name.',
            '!user_name'  => 'User email.',
            '!phone'      => 'User phone.',
            '!site_name'  => 'Human name of the portal.',
            '!portal_url' => 'The portal URL.',
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
            '!award_expiry'       => 'Award expiry date',
            '!portal_name'        => 'Portal name',
            '!portal_image'       => 'Portal logo',
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
