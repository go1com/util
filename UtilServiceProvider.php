<?php

namespace go1\util;

use go1\clients\AccountsClient;
use go1\clients\CurrencyClient;
use go1\clients\EntityClient;
use go1\clients\FirebaseClient;
use go1\clients\GraphinClient;
use go1\clients\LoClient;
use go1\clients\MailClient;
use go1\clients\MqClient;
use go1\clients\NotificationClient;
use go1\clients\PaymentClient;
use go1\clients\PortalClient;
use go1\clients\QueueClient;
use go1\clients\RealtimeClient;
use go1\clients\RulesClient;
use go1\clients\SmsClient;
use go1\clients\UserClient;
use go1\util\lo\LoChecker;
use go1\util\portal\PortalChecker;
use GraphAware\Neo4j\Client\ClientBuilder;
use Elasticsearch\ClientBuilder as EsClientBuilder;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Vectorface\Whip\Whip;

class UtilServiceProvider implements ServiceProviderInterface
{
    public function register(Container $c)
    {
        $c['html'] = function () {
            return Text::defaultPurifier();
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

        $c['go1.client.accounts'] = function (Container $c) {
            return new AccountsClient($c['dbs']['default'], $c['cache'], $c['accounts_name']);
        };

        $c['go1.client.queue'] = function (Container $c) {
            return new QueueClient($c['client'], $c['queue_url']);
        };

        $c['go1.client.es'] = function (Container $c) {
            return EsClientBuilder
                ::create()
                ->setHosts([parse_url($c['es_url'])])
                ->build();
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

        $c['go1.client.graphin'] = function (Container $c) {
            return new GraphinClient($c['client'], $c['graphin_url'], $c['go1.client.mq']);
        };

        $c['go1.client.graph'] = function (Container $c) {
            return ClientBuilder::create()->addConnection('default', $c['graph_url'])->build();
        };

        $c['go1.client.rules'] = function (Container $c) {
            return new RulesClient($c['client'], $c['rules_url'], $c['go1.client.queue']);
        };

        $c['go1.client.currency'] = function (Container $c) {
            return new CurrencyClient($c['cache'], $c['client'], $c['currency_url']);
        };

        $c['go1.client.mq'] = function (Container $c) {
            $options = $c['queueOptions'];

            return new MqClient($options['host'], $options['port'], $options['user'], $options['pass']);
        };

        $c['go1.client.lo'] = function (Container $c) {
            return new LoClient($c['client'], $c['lo_url']);
        };

        $c['go1.client.notification'] = function (Container $c) {
            return new NotificationClient($c['client'], $c['notification_url']);
        };

        $c['go1.client.payment'] = function (Container $c) {
            return new PaymentClient($c['logger'], $c['client'], $c['payment_url']);
        };

        $c['go1.client.realtime'] = function (Container $c) {
            return new RealtimeClient($c['go1.client.mq'], $c['html'], $c['realtime_url']);
        };

        $c['go1.client.firebase'] = function (Container $c) {
            $opt = $c['firebase'];

            return new FirebaseClient($opt['base_uri'], $opt['token'], $opt['default_path']);
        };

        $c['go1.client.sms'] = function (Container $c) {
            return new SmsClient($c['client'], $c['sms_url'], $c['go1.client.queue'], $c['go1.client.mq']);
        };

        $c['go1.client.entity'] = function (Container $c) {
            return new EntityClient($c['client'], $c['entity_url']);
        };
    }
}
