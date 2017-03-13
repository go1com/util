<?php

namespace go1\util\portal;

use Doctrine\DBAL\Connection;
use go1\util\DB;
use stdClass;

class PortalHelper
{
    const STABLE_VERSION = 'v3.0.0';

    const FEATURE_CREDIT                       = 'credit';
    const FEATURE_CREDIT_DEFAULT               = true;
    const FEATURE_SEND_WELCOME_EMAIL           = 'send_welcome_email';
    const FEATURE_SEND_WELCOME_EMAIL_DEFAULT   = true;
    const FEATURE_CUSTOM_SMTP                  = 'custom_smtp';
    const FEATURE_NOTIFY_NEW_ENROLMENT         = 'notify_on_enrolment_create';
    const FEATURE_NOTIFY_NEW_ENROLMENT_DEFAULT = true;
    const DEFAULT_USERS_LICENSES               = 10;
    const DEFAULT_USERS_LICENSES_TIMES         = 4;

    public static function load(Connection $db, $nameOrId)
    {
        $column = is_numeric($nameOrId) ? 'id' : 'title';

        return $db->executeQuery("SELECT * FROM gc_instance WHERE $column = ?", [$nameOrId])->fetch(DB::OBJ);
    }

    public static function nameFromId(Connection $db, int $id)
    {
        return $db->fetchColumn('SELECT title FROM gc_instance WHERE id = ?', [$id]);
    }

    public static function parseConfig(stdClass &$portal)
    {
        if (!isset($portal->configuration)) {
            if (!empty($portal->data)) {
                $portal->data = is_scalar($portal->data) ? json_decode($portal->data) : $portal->data;
                if (!empty($portal->data->configuration)) {
                    $portal->configuration = $portal->data->configuration;
                    unset($portal->data->configuration);
                }
            }
        }
    }

    public static function loadFromLoId(Connection $db, int $loId)
    {
        return $db->executeQuery(
            'SELECT gc_instance.* FROM gc_instance'
            . ' INNER JOIN gc_lo ON gc_instance.id = gc_lo.instance_id'
            . ' WHERE gc_lo.id = ?',
            [$loId]
        )->fetch(DB::OBJ);
    }

    public static function titleFromLoId(Connection $db, int $loId)
    {
        return $db->executeQuery(
            'SELECT gc_instance.title FROM gc_instance'
            . ' INNER JOIN gc_lo ON gc_instance.id = gc_lo.instance_id'
            . ' WHERE gc_lo.id = ?',
            [$loId]
        )->fetchColumn();
    }
}
