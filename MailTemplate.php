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
            '!request_url' => 'Request URL',
            '!id'          => 'Course ID',
            '!user_name'   => 'User full name',
            '!mail'        => 'User mail',
            '!phone'       => 'User phone',
            '!time'        => 'Enquiry time',
            '!title'       => 'Course title',
        ],
    ];

    const ENQUIRY_ACCEPTED = [
        'key'    => 'lo.enquiry.accepted',
        'tokens' => [
            '!title'     => 'Course title',
            '!first'     => 'User first name',
            '!last'      => 'User last name',
            '!site_name' => 'Human name of the portal',
        ],
    ];

    const ENQUIRY_REJECED = [
        'key'    => 'lo.enquiry.rejected',
        'tokens' => [
            '!title'     => 'Course title',
            '!first'     => 'User first name',
            '!last'      => 'User last name',
            '!site_name' => 'Human name of the portal',
        ],
    ];

    const ENQUIRY_NOTIFY_STAFF = [
        'key'    => 'lo.enquiry.notify.staff',
        'tokens' => [/* @TODO */],
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

    const USER_MANUAL_PAYMENT_COURSE = [
        'key'    => 'user.manual.payment.course',
        'tokens' => [
            '!recipient_name' => 'Recipient full name',
            '!user_name'      => 'User full name',
            '!course_name'    => 'Course name',
            '!description'    => 'Enrolment description',
            '!reject_url'     => 'Reject URL',
            '!accept_url'     => 'Accept URL',
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
            '!user_first_name'  => 'User first name',
            '!full_name'        => 'User full name',
            '!course_name'      => 'Course title',
            '!site_name'        => 'Portal title',
            '!pricing_price'    => 'Course price',
            '!pricing_currency' => 'Course price currency',
            '!pricing_tax'      => 'Course price tax',
            '!course_url'       => 'Course URL',
            '!approve_url'      => 'Credit approve URL',
            '!reject_url'       => 'Credit reject URL',
            '!portal_url'       => 'Portal URL',
        ],
    ];

    const CREDIT_REQUEST_ACCEPT = [
        'key'    => 'credit.request.accept',
        'tokens' => [
            '!first_name'   => 'User first name',
            '!manager_name' => 'Manager first name',
            '!course_name'  => 'Course name',
            '!site_name'    => 'Human name of the portal',
            '!course_url'   => 'Course URL',
        ],
    ];

    const CREDIT_REQUEST_REJECT = [
        'key'    => 'credit.request.reject',
        'tokens' => [
            '!first_name'   => 'User first name',
            '!manager_name' => 'Manager first name',
            '!course_name'  => 'Course name',
            '!site_name'    => 'Human name of the portal',
            '!course_url'   => 'Course URL',
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
        ],
    ];

    const ASSIGNMENT_MARKED = [
        'key'    => 'assignment.marked',
        'tokens' => [
            '!li_title'          => 'Learning item title',
            '!li_submission_url' => 'Learning item submission url',
        ],
    ];

    const QUIZ_MARKING = [
        'key'    => 'quiz.marking',
        'tokens' => [
            '!learner_full_name' => 'Learner full name',
            '!quiz_title'        => 'Quiz title',
            '!quiz_url'          => 'Quiz URL',
        ],
    ];

    const QUIZ_MARKED = [
        'key'    => 'quiz.marked',
        'tokens' => [
            '!li_title' => 'Learning item title',
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
