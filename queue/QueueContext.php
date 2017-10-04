<?php

namespace go1\util\queue;

class QueueContext
{
    const ACTION = [
        'user_create' => [
            self::ACTION_USER_CREATE_ADMIN_CREATE,
            self::ACTION_USER_CREATE_REGISTER,
            self::ACTION_USER_CREATE_INVITE,
            self::ACTION_USER_CREATE_IMPORT,
        ],
    ];

    const ACTION_USER_CREATE_ADMIN_CREATE    = 'action.user_create.admin_create';  // context.is_new is required
    const ACTION_USER_CREATE_REGISTER        = 'action.user_create.register';      // context.is_new is required
    const ACTION_USER_CREATE_INVITE          = 'action.user_create.invite';        // context.is_new is required
    const ACTION_USER_CREATE_IMPORT          = 'action.user_create.import';        // context.is_new is required

    const ACTION_ONBOARD_WELCOME             = 'action.onboard.welcome';

    const ACTION_USER_SECONDARY_EMAIL_ADD    = 'action.user_secondary_email.add';
    const ACTION_USER_SECONDARY_EMAIL_VERIFY = 'action.user_secondary_email.verify';
}
