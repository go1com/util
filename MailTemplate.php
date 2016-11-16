<?php

namespace go1\util;

use InvalidArgumentException;
use ReflectionClass;

class MailTemplate
{
    const USER_WELCOME_REGISTER = [
        'key'    => 'user.welcome.register',
        'tokens' => [
            '!user_name'  => 'Machine name of user name, this maybe the email address.',
            '@user_name'  => 'User name.',
            '!site_name'  => 'Human name of the portal.',
            '!portal_url' => 'The portal URL.',
        ],
    ];

    const USER_WELCOME_INVITED = [
        'key'    => 'user.welcome.invite',
        'tokens' => [
            '@user_name'   => 'Name of invited user.',
            '!user_name'   => 'Name of invitee user.',
            '!host_email'  => '',
            '!onetime_url' => 'Onetime login link.',
            '!site_name'   => 'Human name of the portal.',
            '!portal_url'  => 'The portal URL.',
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

    public static function token($key)
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
