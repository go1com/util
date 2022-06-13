<?php

namespace go1\util;

use Aws\Credentials\CredentialProvider;
use Aws\S3\S3Client;
use go1\ElasticsearchCompat\SearchClient;
use go1\ElasticsearchCompat\Version;
use Elasticsearch\ClientBuilder as EsClientBuilder;
use go1\clients\AccountsClient;
use go1\clients\CurrencyClient;
use go1\clients\DownloadPDFClient;
use go1\clients\EckClient;
use go1\clients\EntityClient;
use go1\clients\FirebaseClient;
use go1\clients\GraphinClient;
use go1\clients\PaymentClient;
use go1\clients\QueueClient;
use go1\clients\RealtimeClient;
use go1\clients\RulesClient;
use go1\clients\S3Client as Go1S3Client;
use go1\clients\SchedulerClient;
use go1\clients\SmsClient;
use go1\clients\UtilCoreClientServiceProvider;
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
        if (class_exists(Whip::class)) {
            $c['whip'] = function () {
                return new Whip(Whip::REMOTE_ADDR);
            };
        }

        $c['go1.client.accounts'] = function (Container $c) {
            return new AccountsClient($c['dbs']['default'], $c['cache'], $c['accounts_name']);
        };

        $c['go1.client.queue'] = function (Container $c) {
            return new QueueClient($c['client'], $c['queue_url']);
        };

        $c['go1.client.es.builder'] = $c->factory(function (Container $c) {
            $builder = EsClientBuilder::create();
            $o = $c['esOptions'];

            if ($c->offsetExists('profiler.do')) {
                if ($c->offsetGet('profiler.do')) {
                    $builder->setLogger($c['profiler.collectors.es']);
                }
            }

            $builder->setHosts([parse_url($o['endpoint'])]);
            if (isset($c['go1.client.es.serializer'])) {
                $builder->setSerializer($c['go1.client.es.serializer']);
            }

            return $builder;
        });

        $c['go1.client.es.v8.builder'] = $c->factory(function (Container $c) {
            $builder = EsClientBuilder::create();
            $o = $c['esOptions.v8'];

            if ($c->offsetExists('profiler.do')) {
                if ($c->offsetGet('profiler.do')) {
                    $builder->setLogger($c['profiler.collectors.es']);
                }
            }

            $builder->setHosts([parse_url($o['endpoint'])]);
            if (isset($c['go1.client.es.serializer'])) {
                $builder->setSerializer($c['go1.client.es.serializer']);
            }

            return $builder;
        });

        $c['go1.client.es'] = function (Container $c) {
            /** @var ClientBuilder $builder */
            $builder = $c['go1.client.es.builder'];

            return $builder->build();
        };

        $c['go1.client.es.compat'] = function (Container $c) {
            return new SearchClient(getenv('STATSIG_SERVER_KEY'), getenv("ENV"), [
                Version::ES56 => $c['go1.client.es.builder']->build(),
                Version::ES82_AU => $c['go1.client.es.v8.builder']->build()
            ]);
        };

        $c['go1.client.s3'] = function (Container $c) {
            $o = $c['s3Options'];

            $args = [
                'region'      => $o['region'],
                'version'     => $o['version'],
                'credentials' => CredentialProvider::defaultProvider(),
            ];

            if (getenv('MONOLITH')) {
                // https://github.com/minio/cookbook/blob/master/docs/aws-sdk-for-php-with-minio.md
                $args['endpoint'] = $o['endpoint'];
                $args['use_path_style_endpoint'] = true;
            }

            return new S3Client($args);
        };

        $c['go1.client.graphin'] = function (Container $c) {
            return new GraphinClient($c['client'], $c['graphin_url'], $c['go1.client.mq']);
        };

        $c['go1.client.graph'] = function (Container $c) {
            $neo4jBuilderClass = 'go1\neo4j_builder\Neo4jBuilder';
            $config = [];
            if (class_exists($neo4jBuilderClass)) {
                $config = ['client_class' => $neo4jBuilderClass, ClientBuilder::TIMEOUT_CONFIG_KEY => 30];
            }

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

        $c['go1.client.payment'] = function (Container $c) {
            return new PaymentClient($c['logger'], $c['client'], $c['payment_url']);
        };

        $c['go1.client.realtime'] = function (Container $c) {
            return new RealtimeClient($c['client'], $c['html'], $c['realtime_url']);
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

        // Avoid legacy code to be broken.
        // @TODO: Remove this legacy supporting code
        $hack = -1 == version_compare(Service::VERSION, 'v18.8.5.0');
        $hack = $hack || getenv('MONOLITH');
        if ($hack) {
            $c
                ->register(new UtilCoreServiceProvider)
                ->register(new UtilCoreClientServiceProvider);
        }
    }
}
