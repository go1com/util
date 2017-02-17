<?php

namespace go1\util;

use Doctrine\DBAL\Connection;

class PortalHelper
{
    const STABLE_VERSION = 'v3.0.0';

    const FEATURE_SEND_WELCOME_EMAIL   = 'send_welcome_email';
    const FEATURE_CUSTOM_SMTP          = 'custom_smtp';
    const FEATURE_NOTIFY_NEW_ENROLMENT = 'notify_on_enrolment_create';
    const DEFAULT_USERS_LICENSES       = 10;

    public static function nameFromId(Connection $db, int $id)
    {
        return $db->fetchColumn('SELECT title FROM gc_instance WHERE id = ?', [$id]);
    }
}
