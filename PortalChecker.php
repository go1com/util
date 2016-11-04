<?php

namespace go1\util;

class PortalChecker
{
    const INSTANCE_ENABLED = 1;

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

    public function isEnabled($portal) {
        return isset($portal->status) ? (static::INSTANCE_ENABLED == $portal->status) : false;
    }
}
