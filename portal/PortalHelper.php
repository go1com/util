<?php

namespace go1\util\portal;

use Doctrine\DBAL\Connection;
use go1\clients\MqClient;
use go1\util\DB;
use go1\util\Queue;
use stdClass;

class PortalHelper
{
    const LEGACY_VERSION = 'v2.11.0';
    const STABLE_VERSION = 'v3.0.0';

    const WEBSITE_DOMAIN          = 'go1.com';
    const WEBSITE_PUBLIC_INSTANCE = 'public.mygo1.com';

    const FEATURE_CREDIT                       = 'credit';
    const FEATURE_CREDIT_DEFAULT               = true;
    const FEATURE_SEND_WELCOME_EMAIL           = 'send_welcome_email';
    const FEATURE_SEND_WELCOME_EMAIL_DEFAULT   = true;
    const FEATURE_CUSTOM_SMTP                  = 'custom_smtp';
    const FEATURE_CREDIT_REQUEST               = 'credit_request';
    const FEATURE_CREDIT_REQUEST_DEFAULT       = false;
    const FEATURE_NOTIFY_NEW_ENROLMENT         = 'notify_on_enrolment_create';
    const FEATURE_NOTIFY_NEW_ENROLMENT_DEFAULT = true;

    public static function load(Connection $db, $nameOrId, $columns = '*')
    {
        $column = is_numeric($nameOrId) ? 'id' : 'title';
        $portal = "SELECT {$columns} FROM gc_instance WHERE $column = ?";
        $portal = $db->executeQuery($portal, [$nameOrId])->fetch(DB::OBJ);
        if ($portal && isset($portal->data)) {
            $portal->data = json_decode($portal->data);
        }

        return $portal;
    }

    public static function updateVersion(Connection $db, MqClient $queue, string $version, $portalId)
    {
        if (!$original = self::load($db, $portalId)) {
            return null;
        }

        $db->update('gc_instance', ['version' => $version], ['id' => $portalId]);
        $portal = self::load($db, $portalId);
        $portal->original = $original;
        $queue->publish($portal, Queue::PORTAL_UPDATE);
    }

    public static function idFromName(Connection $db, string $instance)
    {
        return $db->fetchColumn('SELECT id FROM gc_instance WHERE title = ?', [$instance]);
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

                    if (isset($portal->configuration->dashboard_blocks) && is_scalar($portal->configuration->dashboard_blocks)) {
                        $portal->configuration->dashboard_blocks = json_decode($portal->configuration->dashboard_blocks);
                    }
                }
            }
        }
    }

    public static function loadFromLoId(Connection $db, int $loId)
    {
        $portal = &DB::cache(__METHOD__, []);

        if (isset($portal[$loId])) {
            return $portal[$loId];
        }

        return $portal[$loId] = $db->executeQuery(
            'SELECT gc_instance.* FROM gc_instance'
            . ' INNER JOIN gc_lo ON gc_instance.id = gc_lo.instance_id'
            . ' WHERE gc_lo.id = ?',
            [$loId])->fetch(DB::OBJ);
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

    public static function logo(stdClass $portal)
    {
        self::parseConfig($portal);

        $logo = $portal->data->files->logo
            ?? ($portal->data->configuration->logo
                ?? ($portal->data->logo ?? ''));

        return (filter_var($logo, FILTER_VALIDATE_URL) === false) ? ('https:' . $logo) : $logo;
    }
}
