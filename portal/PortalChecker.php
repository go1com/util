<?php

namespace go1\util\portal;

use Doctrine\DBAL\Connection;
use go1\util\collection\PortalCollectionConfiguration;
use go1\util\DB;
use go1\util\user\Roles;
use stdClass;

class PortalChecker
{
    public function load(Connection $db, $instance)
    {
        $column = is_numeric($instance) ? 'id' : 'title';

        return $db->executeQuery("SELECT * FROM gc_instance WHERE {$column} = ?", [$instance])->fetch(DB::OBJ);
    }

    public function isVirtual($portal)
    {
        PortalHelper::parseConfig($portal);

        return !empty($portal->configuration->is_virtual) ? true : (version_compare($portal->version, 'v3.0.0-alpha1') >= 0);
    }

    public function isLegacy($portal)
    {
        return !$this->isVirtual($portal);
    }

    public function getPrimaryDomain($portal)
    {
        PortalHelper::parseConfig($portal);

        return !empty($portal->configuration->primary_domain) ? $portal->configuration->primary_domain : $portal->title;
    }

    public function getSiteName($portal)
    {
        PortalHelper::parseConfig($portal);

        return !empty($portal->configuration->site_name) ? $portal->configuration->site_name : $portal->title;
    }

    public function isEnabled($portal)
    {
        return isset($portal->status) ? (PortalStatuses::ENABLED == $portal->status) : false;
    }

    public function getPublicKey($portal)
    {
        PortalHelper::parseConfig($portal);

        return !empty($portal->data->public_key) ? $portal->data->public_key : false;
    }

    public function canSendEmail($portal, $key)
    {
        PortalHelper::parseConfig($portal);

        return !empty($portal->configuration->{$key}) ? $portal->configuration->{$key} : true;
    }

    public function allowPublicWriting($portal)
    {
        PortalHelper::parseConfig($portal);

        return !empty($portal->configuration->public_writing) ? $portal->configuration->public_writing : false;
    }

    public function allowSendingWelcomeEmail($portal)
    {
        if ($this->isLegacy($portal)) {
            PortalHelper::parseConfig($portal);

            if (!empty($portal->configuration)) {
                if (isset($portal->configuration->{PortalHelper::FEATURE_SEND_WELCOME_EMAIL})) {
                    if (!$portal->configuration->{PortalHelper::FEATURE_SEND_WELCOME_EMAIL}) {
                        return false;
                    }
                }
            }
        }

        return PortalHelper::FEATURE_SEND_WELCOME_EMAIL_DEFAULT;
    }

    public function allowNotifyEnrolment($portal)
    {
        if ($this->isLegacy($portal)) {
            PortalHelper::parseConfig($portal);

            if (!empty($portal->configuration)) {
                if (isset($portal->configuration->{PortalHelper::FEATURE_NOTIFY_NEW_ENROLMENT})) {
                    if (!$portal->configuration->{PortalHelper::FEATURE_NOTIFY_NEW_ENROLMENT}) {
                        return false;
                    }
                }
            }
        }

        return PortalHelper::FEATURE_NOTIFY_NEW_ENROLMENT_DEFAULT;
    }

    public function useCustomSMTP($portal)
    {
        PortalHelper::parseConfig($portal);

        return !empty($portal->configuration->{PortalHelper::FEATURE_CUSTOM_SMTP});
    }

    public function allowCredit($portal)
    {
        PortalHelper::parseConfig($portal);

        if (isset($portal->configuration->{PortalHelper::FEATURE_CREDIT})) {
            return $portal->configuration->{PortalHelper::FEATURE_CREDIT};
        }

        return PortalHelper::FEATURE_CREDIT_DEFAULT;
    }

    public function allowRegister($portal)
    {
        PortalHelper::parseConfig($portal);

        return $portal->configuration->modulesEnabled->allowRegister ?? true;
    }

    /**
     * @param object $portal
     * @param string $uri
     * @param string $prefix
     * @param bool   $replacePublicDomain If set to false, does not replace public.mygo1.com with www.go1.com
     * @return string
     */
    public function buildLink($portal, $uri, $prefix = '', $replacePublicDomain = true)
    {
        $uri = ltrim($uri, '/');
        $env = getenv('ENV') ?: 'production';
        switch ($env) {
            case 'production':
                if ($replacePublicDomain && PortalHelper::WEBSITE_PUBLIC_INSTANCE == $portal->title) {
                    $domain = PortalHelper::WEBSITE_DOMAIN;
                    if (stripos($domain, 'www.') === false) {
                        $domain = 'www.' . $domain;
                    }
                } else {
                    $domain = $this->getPrimaryDomain($portal);
                    $domain = $this->isVirtual($portal) ? "{$domain}/p" : "{$domain}/webapp";
                }
                break;

            case 'staging':
                $domain = PortalHelper::WEBSITE_STAGING_INSTANCE . '/p';
                break;

            case 'qa':
                $domain = PortalHelper::WEBSITE_QA_INSTANCE . '/p';
                break;

            case 'dev':
                $domain = PortalHelper::WEBSITE_DEV_INSTANCE . '/p';
                break;
        }

        if (getenv('MONOLITH') && getenv('ENV_HOSTNAME')) {
            $domain = getenv('ENV_HOSTNAME') . '/p';
        }

        if (getenv('ENV_HOSTNAME_QA')) {
            $domain = getenv('ENV_HOSTNAME_QA') . '/p';
        }

        return (PortalHelper::WEBSITE_DOMAIN == $domain) ? "https://{$domain}/{$uri}" : "https://{$domain}/{$prefix}#/{$uri}";
    }

    public function allowPublicGroup($portal): bool
    {
        PortalHelper::parseConfig($portal);
        if (!empty($portal->configuration)) {
            return boolval($portal->configuration->public_group ?? false)
                ?: boolval($portal->configuration->publicGroupsEnabled ?? false);
        }

        return false;
    }

    public static function allowDiscussion($portal): bool
    {
        PortalHelper::parseConfig($portal);
        if (!empty($portal->configuration)) {
            return boolval($portal->configuration->discussion ?? false)
                ?: boolval($portal->configuration->discussionEnabled ?? false);
        }

        return true;
    }

    public static function allowUserInvite($portal): bool
    {
        PortalHelper::parseConfig($portal);

        return boolval($portal->configuration->user_invite ?? true);
    }

    public static function allowPublicProfile($portal): bool
    {
        PortalHelper::parseConfig($portal);

        return boolval($portal->configuration->public_profiles ?? false);
    }

    public static function allowUserPayment($portal): bool
    {
        PortalHelper::parseConfig($portal);

        return boolval($portal->configuration->user_payment ?? true);
    }

    public static function allowMarketplace($portal): bool
    {
        PortalHelper::parseConfig($portal);

        return (!empty($portal->features))
            ? boolval($portal->features->marketplace ?? true)
            : true;
    }

    public static function allowNotifyRemindMajorEventByRole($portal, string $role = Roles::STUDENT): bool
    {
        PortalHelper::parseConfig($portal);

        $config = (array) $portal->configuration->{PortalHelper::FEATURE_NOTIFY_REMIND_MAJOR_EVENT} ?? [];

        return boolval($config[$role] ?? false);
    }

    public static function selectedContentSelections(stdClass $portal): bool
    {
        $collections = PortalHelper::collections($portal);
        if (in_array(PortalCollectionConfiguration::SUBSCRIBE, $collections)) {
            return true;
        }

        if (PortalChecker::allowMarketplace($portal)) {
            if (in_array(PortalCollectionConfiguration::FREE, $collections)
                || in_array(PortalCollectionConfiguration::PAID, $collections)) {
                return true;
            }
        }

        return false;
    }
}
