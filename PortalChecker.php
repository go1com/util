<?php

namespace go1\util;

use Doctrine\DBAL\Connection;

class PortalChecker
{
    private function prepare(&$portal)
    {
        if (!isset($portal->configuration) && !empty($portal->data)) {
            $portal->data = is_scalar($portal->data) ? json_decode($portal->data) : $portal->data;
            if (!empty($portal->data->configuration)) {
                $portal->configuration = $portal->data->configuration;
                unset($portal->data->configuration);
            }
        }
    }

    public function load(Connection $db, $instance)
    {
        $column = is_numeric($instance) ? 'id' : 'title';

        return $db->executeQuery("SELECT * FROM gc_instance WHERE {$column} = ?", [$instance])->fetch(DB::OBJ);
    }

    public function isVirtual($portal)
    {
        $this->prepare($portal);

        return !empty($portal->configuration->is_virtual) ? true : (version_compare($portal->version, 'v3.0.0-alpha1') >= 0);
    }

    public function getPrimaryDomain($portal)
    {
        $this->prepare($portal);

        return !empty($portal->configuration->primary_domain) ? $portal->configuration->primary_domain : $portal->title;
    }

    public function getSiteName($portal)
    {
        $this->prepare($portal);

        return !empty($portal->configuration->site_name) ? $portal->configuration->site_name : $portal->title;
    }

    public function isEnabled($portal)
    {
        return isset($portal->status) ? (PortalStatuses::ENABLED == $portal->status) : false;
    }

    public function getPublicKey($portal)
    {
        $this->prepare($portal);

        return !empty($portal->data->public_key) ? $portal->data->public_key : false;
    }

    public function canSendEmail($portal, $key)
    {
        $this->prepare($portal);

        return !empty($portal->configuration->{$key}) ? $portal->configuration->{$key} : true;
    }

    public function allowPublicWriting($portal)
    {
        $this->prepare($portal);

        return !empty($portal->configuration->public_writing) ? $portal->configuration->public_writing : false;
    }

    public function buildLink($portal, $uri)
    {
        $domain = $this->getPrimaryDomain($portal);
        $uri = ltrim($uri, '/');

        return ($this->isVirtual($portal))
            ? "https://{$domain}/p/#/{$uri}"
            : "https://{$domain}/webapp/#/{$uri}";
    }
}
