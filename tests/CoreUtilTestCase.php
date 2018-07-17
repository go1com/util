<?php

namespace go1\util\tests;

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\Schema;
use go1\util\CoreServiceProvider;
use go1\util\DB;
use go1\util\schema\InstallTrait;
use go1\util\schema\mock\UserMockTrait;
use go1\util\Service;
use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Slim\Container;

class CoreUtilTestCase extends TestCase
{
    use InstallTrait;
    use UserMockTrait;
    use QueueMockTrait;

    /** @var  Connection */
    protected $db;
    protected $queue;
    protected $log;

    public function setUp()
    {
        $this->db = DriverManager::getConnection(['url' => 'sqlite://sqlite::memory:']);
        $this->installGo1Schema($this->db, false);

        DB::install($this->db, [
            function (Schema $schema) {
                $this->setupDatabaseSchema($schema);
            },
        ]);

        $c = $this->getContainer();
        $this->mockMqClient($c);
        $this->queue = $c['go1.client.mq'];
    }

    protected function setupDatabaseSchema(Schema $schema)
    {
        # Extra database setup, test cases can safely override this.
    }

    protected function getContainer()
    {
        $logger = $this
            ->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['error'])
            ->getMockForAbstractClass();

        $logger
            ->expects($this->any())
            ->method('error')
            ->willReturnCallback(function ($message) {
                $this->log['error'][] = $message;
            });

        return (new Container(['accounts_name' => 'accounts.test']))
            ->register(new CoreServiceProvider, [
                    'logger'       => $logger,
                    'client'       => new Client,
                    'cache'        => new ArrayCache,
                    'queueOptions' => [
                        'host' => '172.31.11.129',
                        'port' => '5672',
                        'user' => 'go1',
                        'pass' => 'go1',
                    ],
                ] + Service::urls(['queue', 'user', 'mail', 'portal', 'rules', 'currency', 'lo', 'sms', 'graphin', 's3', 'realtime'], 'qa')
            );
    }
}
