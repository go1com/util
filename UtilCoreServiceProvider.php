<?php

namespace go1\util;

use go1\util\lo\LoChecker;
use go1\util\portal\PortalChecker;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class UtilCoreServiceProvider implements ServiceProviderInterface
{
    public function register(Container $c)
    {
        $c['html'] = function () { return Text::defaultPurifier(); };
        $c['access_checker'] = function () { return new AccessChecker; };
        $c['portal_checker'] = function () { return new PortalChecker; };
        $c['lo_checker'] = function () { return new LoChecker; };
    }
}
