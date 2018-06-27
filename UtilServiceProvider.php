<?php

namespace go1\util;

use Aws\Credentials\CredentialProvider;
use Aws\Credentials\Credentials;
use Aws\ElasticsearchService\ElasticsearchPhpHandler;
use Aws\S3\S3Client;
use Elasticsearch\ClientBuilder as EsClientBuilder;
use go1\clients\AccountsClient;
use go1\clients\CurrencyClient;
use go1\clients\DownloadPDFClient;
use go1\clients\EckClient;
use go1\clients\EntityClient;
use go1\clients\FirebaseClient;
use go1\clients\GraphinClient;
use go1\clients\LoClient;
use go1\clients\MailClient;
use go1\clients\MqClient;
use go1\clients\PaymentClient;
use go1\clients\PortalClient;
use go1\clients\QueueClient;
use go1\clients\RealtimeClient;
use go1\clients\RulesClient;
use go1\clients\S3Client as Go1S3Client;
use go1\clients\SchedulerClient;
use go1\clients\SmsClient;
use go1\clients\UserClient;
use go1\neo4j_builder\Neo4jBuilder;
use go1\util\lo\LoChecker;
use go1\util\portal\PortalChecker;
use go1\util\toggle\FeatureToggleClient;
use GraphAware\Neo4j\Client\ClientBuilder;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Qandidate\Toggle\ToggleCollection\PredisCollection;
use Qandidate\Toggle\ToggleManager;
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
            $builder = EsClientBuilder::create();

            if ($o = $c['esOptions']) {
                if ($o['credential']) {
                    $provider = CredentialProvider::fromCredentials(new Credentials($o['key'], $o['secret']));
                    $builder->setHandler(new ElasticsearchPhpHandler($o['region'], $provider));
                }
            }

            if ($c->offsetExists('profiler.do') && $c->offsetGet('profiler.do')) {
                $builder->setLogger($c['profiler.collectors.es']);
            }

            $builder->setHosts([parse_url($o['endpoint'])]);
            if (isset($c['go1.client.es.serializer'])) {
                $builder->setSerializer($c['go1.client.es.serializer']);
            }
            return $builder->build();
        };

        $c['go1.client.s3'] = function (Container $c) {
            $o = $c['s3Options'];

            $args = [
                'region'      => $o['region'],
                'version'     => $o['version'],
                'credentials' => new Credentials($o['key'], $o['secret']),
            ];

            if (getenv('MONOLITH')) {
                // https://github.com/minio/cookbook/blob/master/docs/aws-sdk-for-php-with-minio.md
                $args['endpoint'] = $o['endpoint'];
                $args['use_path_style_endpoint'] = true;
            }

            return new S3Client($args);
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
            $config = class_exists('Neo4jBuilder')
                ? ['client_class' => Neo4jBuilder::class, ClientBuilder::TIMEOUT_CONFIG_KEY => 30]
                : [];
            $builder = ClientBuilder::create($config);

            $builder->addConnection('default', $c['graph_url']);

            if ($c->offsetExists('profiler.do') && $c->offsetGet('profiler.do')) {
                $c['profiler.collectors.neo4j']->attachEventListeners($builder);
            }

            return $builder->build();
        };

        $c['go1.client.rules'] = function (Container $c) {
            return new RulesClient($c['client'], $c['rules_url'], $c['go1.client.queue']);
        };

        $c['go1.client.currency'] = function (Container $c) {
            return new CurrencyClient($c['cache'], $c['client'], $c['currency_url']);
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

        $c['go1.client.payment'] = function (Container $c) {
            return new PaymentClient($c['logger'], $c['client'], $c['payment_url']);
        };

        $c['go1.client.realtime'] = function (Container $c) {
            return new RealtimeClient($c['go1.client.mq'], $c['html'], $c['realtime_url']);
        };

        $c['go1.client.firebase'] = function (Container $c) {
            $o = $c['firebase'];

            return new FirebaseClient($o['base_uri'], $o['token'], $o['default_path']);
        };

        $c['go1.client.sms'] = function (Container $c) {
            return new SmsClient($c['client'], $c['sms_url'], $c['go1.client.queue'], $c['go1.client.mq']);
        };

        $c['go1.client.entity'] = function (Container $c) {
            return new EntityClient($c['client'], $c['entity_url']);
        };

        $c['go1.client.eck'] = function (Container $c) {
            return new EckClient($c['client'], $c['eck_url']);
        };

        $c['go1.client.download-pdf'] = function (Container $c) {
            return new DownloadPDFClient($c['client'], $c['wkhtmltopdf_url']);
        };

        $c['go1.client.go1s3'] = function (Container $c) {
            return new Go1S3Client($c['client'], $c['s3_url']);
        };

        $c['go1.client.scheduler'] = function (Container $c) {
            return new SchedulerClient($c['client'], $c['logger'], $c['scheduler_url']);
        };

        if ($c->offsetExists('toggleOptions')) {
            $c['toggle.manager.collection'] = function (Container $c) {
                $o = $c['toggleOptions'];
                return new PredisCollection($o['namespace'], $c['cache.predis']);
            };
        }

        $c['toggle.manager'] = function (Container $c) {
            return new ToggleManager($c['toggle.manager.collection']);
        };

        $c['toggle.manager.client'] = function (Container $c) {
            return new FeatureToggleClient($c['toggle.manager']);
        };
    }
}
