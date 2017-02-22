<?php

namespace go1\clients;

use GraphAware\Neo4j\Client\ClientBuilder;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ClientServiceProvider implements ServiceProviderInterface
{
    public function register(Container $c)
    {
        $c['go1.client.accounts'] = function (Container $c) {
            return new AccountsClient($c['dbs']['default'], $c['cache'], $c['accounts_name']);
        };

        $c['go1.client.queue'] = function (Container $c) {
            return new QueueClient($c['client'], $c['queue_url']);
        };

        $c['go1.client.user'] = function (Container $c) {
            return new UserClient($c['client'], $c['user_url']);
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
    }
}
