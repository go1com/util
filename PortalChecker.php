<?php

namespace go1\util;

class PortalChecker
{
    public function isVirtual($portal)
    {
        if (!empty($portal->configuration->is_virtual)) {
            return true;
        }

        if (!empty($portal->data) && $data = json_decode($portal->data)) {
            if (!empty($data->configuration)) {
                return true;
            }
        }

        return version_compare($portal->version, 'v3.0.0') >= 0;
    }
}

