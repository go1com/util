<?php

namespace go1\util;

use ReflectionObject;
use Silex\Application;
use Silex\Controller;
use Silex\ControllerCollection;

class Service
{
    const VERSION = 'v18.4.3.0';

    public static function cacheOptions($root)
    {
        return (getenv('CACHE_BACKEND') && 'memcached' === getenv('CACHE_BACKEND'))
            ? ['backend' => 'memcached', 'host' => getenv('CACHE_HOST'), 'port' => getenv('CACHE_PORT')]
            : ['backend' => 'filesystem', 'directory' => $root . '/cache'];
    }

    public static function queueOptions()
    {
        return [
            'host' => getenv('QUEUE_HOST') ?: '172.31.11.129',
            'port' => getenv('QUEUE_PORT') ?: '5672',
            'user' => getenv('QUEUE_USER') ?: 'go1',
            'pass' => getenv('QUEUE_PASSWORD') ?: 'go1',
        ];
    }

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

    public static function elasticSearchIndex(): string
    {
        !defined('ES_INDEX') && define('ES_INDEX', getenv('ES_INDEX') ?: 'go1_dev');

        return ES_INDEX;
    }

    public static function isLocalIp(): bool
    {
        $localIps = getenv('LOCAL_IPS');
        if ($localIps) {
            $ip = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null);
            if ($ip) {
                $localIps = explode(',', $localIps);
                foreach ($localIps as $localIp) {
                    if (false !== strpos($ip, $localIp)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Simple method to print application resources.
     *
     * @param Application $app
     */
    public static function endpointsInfo(Application $app)
    {
        $app->boot();

        /** @var ControllerCollection $controllers */
        $controllers = $app['controllers'];
        $rControllers = new ReflectionObject($controllers);
        $pControllers = $rControllers->getProperty('controllers');
        $pControllers->setAccessible(true);

        /** @var Controller $ctrl */
        foreach ($pControllers->getValue($controllers) as $ctrl) {
            if ($ctrl instanceof Controller) {
                $route = $ctrl->getRoute();

                foreach ($route->getMethods() as $method) {
                    echo '- ' . str_pad($method, 7) . ' ' . str_pad($route->getPath(), 70);

                    if ($service = $route->getDefault('_controller')) {
                        if (is_scalar($service)) {
                            echo 'Handler: ' . $service;
                        }
                    }

                    echo "\n";
                }
            }
        }
    }

    /**
     * @param      $env
     * @param bool $public
     * @return string
     */
    public static function gatewayUrl($env, $public = false)
    {
        if (!empty($url = getenv('GATEWAY_URL'))) {
            return $url;
        }

        if ($public) {
            $suffix = 'production' === $env ? '' : "-{$env}";

            return "https://api{$suffix}.go1.co";
        }

        return self::url('gateway', $env);
    }
}
