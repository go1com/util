<?php

namespace go1\util;

use HTMLPurifier;
use HTMLPurifier_Config;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Vectorface\Whip\Whip;

class UtilServiceProvider implements ServiceProviderInterface
{
    public function register(Container $c)
    {
        $c['html'] = function () {
            $config = HTMLPurifier_Config::createDefault();
            $config->set('Cache.DefinitionImpl', null);

            return new HTMLPurifier($config);
        };

        if (class_exists(Whip::class)) {
            $c['whip'] = function () {
                return new Whip(Whip::REMOTE_ADDR);
            };
        }

        $c['access_checker'] = function () {
            return new AccessChecker;
        };

        $c['portal_checker'] = function () {
            return new PortalChecker;
        };

        $c['lo_checker'] = function () {
            return new LoChecker;
        };
    }
}
