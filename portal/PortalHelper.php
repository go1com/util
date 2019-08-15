<?php

namespace go1\util\portal;

use Doctrine\DBAL\Connection;
use go1\clients\MqClient;
use go1\clients\UserClient;
use go1\util\collection\PortalCollectionConfiguration;
use go1\util\DB;
use go1\util\edge\EdgeTypes;
use go1\util\queue\Queue;
use go1\util\user\UserHelper;
use stdClass;
use Exception;

class PortalHelper
{
    const LEGACY_VERSION = 'v2.11.0';
    const STABLE_VERSION = 'v3.0.0';

    const WEBSITE_DOMAIN             = 'www.go1.com';
    const WEBSITE_PUBLIC_INSTANCE    = 'public.mygo1.com';
    const WEBSITE_STAGING_INSTANCE   = 'staging.mygo1.com';
    const WEBSITE_QA_INSTANCE        = 'qa.mygo1.com';
    const WEBSITE_DEV_INSTANCE       = 'dev.mygo1.com';
    CONST CUSTOM_DOMAIN_DEFAULT_HOST = 'go1portals.com';

    const LANGUAGE                             = 'language';
    const LANGUAGE_DEFAULT                     = 'en';
    const LOCALE                               = 'locale';
    const LOCALE_DEFAULT                       = 'AU';
    const FEATURE_CREDIT                       = 'credit';
    const FEATURE_CREDIT_DEFAULT               = true;
    const FEATURE_SEND_WELCOME_EMAIL           = 'send_welcome_email';
    const FEATURE_SEND_WELCOME_EMAIL_DEFAULT   = true;
    const FEATURE_CUSTOM_SMTP                  = 'custom_smtp';
    const FEATURE_CREDIT_REQUEST               = 'credit_request';
    const FEATURE_CREDIT_REQUEST_DEFAULT       = false;
    const FEATURE_NOTIFY_NEW_ENROLMENT         = 'notify_on_enrolment_create';
    const FEATURE_NOTIFY_NEW_ENROLMENT_DEFAULT = true;
    const FEATURE_NOTIFY_REMIND_MAJOR_EVENT    = 'notify_remind_major_event';
    const TIMEZONE_DEFAULT                     = "Australia/Brisbane";
    const COLLECTIONS                          = 'collections';
    const COLLECTIONS_DEFAULT                  = [
        PortalCollectionConfiguration::FREE,
        PortalCollectionConfiguration::PAID,
        PortalCollectionConfiguration::SUBSCRIBE,
        PortalCollectionConfiguration::SHARE,
    ];

    public static function load(Connection $go1, $nameOrId, $columns = '*', bool $aliasSupport = false, bool $includePortalData = false): ?stdClass
    {
        $column = is_numeric($nameOrId) ? 'id' : 'title';
        $portal = "SELECT {$columns} FROM gc_instance WHERE {$column} = ? ";
        $portal = $go1->executeQuery($portal, [$nameOrId])->fetch(DB::OBJ);

        if ($portal) {
            $portal->data = isset($portal->data) ? (object) json_decode($portal->data) : new stdClass();

            if ($includePortalData) {
                $portal->data->portal_data = self::loadPortalDataById($go1, (int) $portal->id);
            }

            return $portal;
        }

        if ($aliasSupport && !is_numeric($nameOrId)) {
            $domainId = 'SELECT id FROM gc_domain WHERE title = ?';
            $domainId = $go1->fetchColumn($domainId, [$nameOrId]);
            if ($domainId) {
                $portalName = 'SELECT source_id FROM gc_ro WHERE type = ? AND target_id = ?';
                $portalName = $go1->fetchColumn($portalName, [EdgeTypes::HAS_DOMAIN, $domainId]);

                return $portalName ? self::load($go1, $portalName) : null;
            }
        }

        return null;
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

        if (!isset($portal->features)) {
            if (!empty($portal->data)) {
                $portal->data = is_scalar($portal->data) ? json_decode($portal->data) : $portal->data;
                if (!empty($portal->data->features)) {
                    $portal->features = $portal->data->features;
                    unset($portal->data->features);
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

        $logo = $portal->data->files->logo ?? ($portal->data->configuration->logo ?? ($portal->data->logo ?? ''));
        if (!$logo) {
            return $logo;
        }

        return (filter_var($logo, FILTER_VALIDATE_URL) === false) ? ('https:' . $logo) : $logo;
    }

    public static function roles(Connection $db, string $portalName)
    {
        $roles = $db->executeQuery('SELECT id, name FROM gc_role WHERE instance = ?', [$portalName])->fetchAll(DB::OBJ);

        return array_combine(array_column($roles, 'id'), array_column($roles, 'name'));
    }

    public static function timezone(stdClass $portal)
    {
        self::parseConfig($portal);

        return $portal->configuration->timezone ?? self::TIMEZONE_DEFAULT;
    }

    public static function portalAdminIds(UserClient $userClient, string $portalName): array
    {
        foreach ($userClient->findAdministrators($portalName, true) as $admin) {
            $adminIds[] = $admin->id;
        }

        return $adminIds ?? [];
    }

    public static function portalAdmins(Connection $db, UserClient $userClient, string $portalName): array
    {
        $adminIds = self::portalAdminIds($userClient, $portalName);

        return !$adminIds ? [] : UserHelper::loadMultiple($db, array_map('intval', $adminIds));
    }

    public static function language(stdClass $portal)
    {
        self::parseConfig($portal);

        return $portal->configuration->{self::LANGUAGE} ?? self::LANGUAGE_DEFAULT;
    }

    public static function locale(stdClass $portal)
    {
        self::parseConfig($portal);

        return $portal->configuration->{self::LOCALE} ?? self::LOCALE_DEFAULT;
    }

    public static function collections(stdClass $portal): array
    {
        self::parseConfig($portal);

        return $portal->configuration->{self::COLLECTIONS} ?? self::COLLECTIONS_DEFAULT;
    }

    public static function loadPortalDataById(Connection $db, int $portalId)
    {
        return $db->executeQuery('SELECT * FROM portal_data WHERE id = ?', [$portalId])->fetch(DB::OBJ);
    }

    public static function getDomainDNSRecords($name): array
    {
        foreach (dns_get_record($name, DNS_A) as $mappingDomain => $mapping) {
            isset($mapping['ip']) && $ips[] = $mapping['ip'];
        }

        return $ips ?? [];
    }

    public static function validateCustomDomainDNS(string $domain): bool
    {
        $GO1Ips = self::getDomainDNSRecords(self::CUSTOM_DOMAIN_DEFAULT_HOST);
        $domainIps = self::getDomainDNSRecords($domain);
        $validated = array_intersect($GO1Ips, $domainIps);

        return sizeof($validated) > 0;
    }

    public static function isSSLEnabledDomain(string $domain): bool
    {
        try {
            $streamContext = stream_context_create(["ssl" => ["capture_peer_cert" => true]]);
            $read = fopen("https://" . $domain, "rb", false, $streamContext);
            $response = stream_context_get_params($read);
            $enabled = !!$response["options"]["ssl"]["peer_certificate"];

            return $enabled;
        } catch (Exception $e) {
            return false;
        }
    }

}
