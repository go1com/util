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
        'tokens' => [/* @TODO */],
    ];

    const ENQUIRY_NOTIFY_STAFF = [
        'key'    => 'lo.enquiry.notify.staff',
        'tokens' => [/* @TODO */],
    ];

    const USER_ENROL_COURSE = [
        'key'    => 'user.enrol.course',
        'tokens' => [
            '@first_name'     => 'First name of user.',
            '!course_name'    => 'Course name.',
        ],
    ];

    const USER_MANUAL_PAYMENT_COURSE = [
        'key'    => 'user.manual.payment.course',
        'tokens' => [
            '!full_name'        => 'Full name.',
            '!user_name'        => 'User mail.',
            '!course_name'      => 'Course name.',
            '!course_url'       => 'Course URL.',
            '!reject_url'       => 'Reject URL.',
            '!accept_url'       => 'Accept URL.'
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
            '!full_name'    => 'User full name.',
            '!user_name'    => 'User email.',
            '!phone'        => 'User phone.',
            '!site_name'    => 'Human name of the portal.',
            '!portal_url'   => 'The portal URL.'
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
