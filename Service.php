<?php

namespace go1\util;

class Service
{
    public static function accountsName(string $env): string
    {
        switch ($env) {
            case 'production':
            case 'staging':
                return 'accounts.gocatalyze.com';

            default:
                return 'accounts-dev.gocatalyze.com';
        }
    }

    public static function urls(array $names, string $env, string $pattern = null): array
    {
        foreach ($names as $name) {
            $urls["{$name}_url"] = static::url($name, $env, $pattern);
        }

        return !empty($urls) ? $urls : [];
    }

    public static function url(string $name, string $env, string $pattern = null): string
    {
        $pattern = $pattern ?: 'http://SERVICE.ENVIRONMENT.go1.service';

        // There are some services don't have staging instance yet.
        if (in_array($name, ['rules'])) {
            $env = 'production';
        }

        return str_replace(['SERVICE', 'ENVIRONMENT'], [$name, $env], $pattern);
    }

    /**
     * This method is only for dev environment for now.
     *
     * The container's /etc/resolver.conf, change nameserver to
     *
     *  nameserver 172.31.10.148
     *
     * @param string $env
     * @param string $name
     * @return string[]
     */
    public static function ipPort(string $env, string $name)
    {
        $records = dns_get_record("$env.$name.service.consul", DNS_SRV);
        if ($records) {
            $service = &$records[0];

            return [$service['target'], $service['port']];
        }
    }
}
