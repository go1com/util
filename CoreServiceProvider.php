<?php

namespace go1\util;

use go1\clients\LoClient;
use go1\clients\MailClient;
use go1\clients\MqClient;
use go1\clients\PortalClient;
use go1\clients\UserClient;
use go1\util\lo\LoChecker;
use go1\util\portal\PortalChecker;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class CoreServiceProvider implements ServiceProviderInterface
{
    public function register(Container $c)
    {
        $c['html'] = function () {
            return Text::defaultPurifier();
        };

        $c['access_checker'] = function () {
            return new AccessChecker;
        };

        $c['portal_checker'] = function () {
            return new PortalChecker;
        };

        $c['lo_checker'] = function () {
            return new LoChecker;
        };

        $c['go1.client.user'] = function (Container $c) {
            return new UserClient($c['client'], $c['user_url'], $c['go1.client.mq']);
        };

        $c['go1.client.mail'] = function (Container $c) {
            return new MailClient($c['go1.client.mq']);
        };

        $c['go1.client.portal'] = function (Container $c) {
            return new PortalClient($c['client'], $c['portal_url'], $c['cache']);
        };

        $c['go1.client.mq'] = function (Container $c) {
            $logger = null;
            $o = $c['queueOptions'];

            if ($c->offsetExists('profiler.do') && $c->offsetGet('profiler.do')) {
                $logger = $c['profiler.collectors.mq'];
            }

            $currentRequest = $c->offsetExists('request_stack') ? $c['request_stack']->getCurrentRequest() : null;

            return new MqClient($o['host'], $o['port'], $o['user'], $o['pass'], $logger, $c['access_checker'], $c, $currentRequest);
        };

        $c['go1.client.lo'] = function (Container $c) {
            return new LoClient($c['client'], $c['lo_url'], $c['go1.client.mq']);
        };
    }
}
