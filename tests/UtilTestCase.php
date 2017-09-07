<?php

namespace go1\util\tests;

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\Schema;
use go1\clients\MqClient;
use go1\util\DB;
use go1\util\plan\PlanRepository;
use go1\util\schema\AssignmentSchema;
use go1\util\schema\AwardSchema;
use go1\util\schema\CreditSchema;
use go1\util\schema\EckSchema;
use go1\util\schema\InstallTrait;
use go1\util\schema\mock\UserMockTrait;
use go1\util\Service;
use go1\util\task\Task;
use go1\util\task\TaskSchema;
use go1\util\UtilServiceProvider;
use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;
use Pimple\Container;
use Psr\Log\LoggerInterface;

abstract class UtilTestCase extends TestCase
{
    use InstallTrait;
    use UserMockTrait;

    /** @var  Connection */
    protected $db;
    protected $queue;
    protected $queueMessages;
    protected $taskService;

    public function setUp()
    {
        $this->db = DriverManager::getConnection(['url' => 'sqlite://sqlite::memory:']);
        $this->installGo1Schema($this->db, false);

        DB::install($this->db, [
            function (Schema $schema) {
                AwardSchema::install($schema);
                AssignmentSchema::install($schema);
                CreditSchema::install($schema);
                EckSchema::install($schema);
                PlanRepository::install($schema);
                if ($this->taskService) {
                    TaskSchema::install($schema, $this->taskService);
                }
            },
        ]);

        $this->queue = $this->getMockBuilder(MqClient::class)->setMethods(['publish', 'queue'])->disableOriginalConstructor()->getMock();
        $this->queue
            ->method('publish')
            ->willReturnCallback(function ($body, $routingKey) {
                $this->queueMessages[$routingKey][] = $body;
            });

        $this->queue
            ->method('queue')
            ->willReturnCallback(function ($body, $routingKey) {
                $this->queueMessages[$routingKey][] = $body;
            });
    }

    protected function getContainer()
    {
        $logger = $this
            ->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['error'])
            ->getMockForAbstractClass();

        return (new Container(['accounts_name' => 'accounts.test']))
            ->register(new UtilServiceProvider, [
                    'logger'       => $logger,
                    'client'       => new Client,
                    'cache'        => new ArrayCache,
                    'queueOptions' => [
                        'host' => '172.31.11.129',
                        'port' => '5672',
                        'user' => 'go1',
                        'pass' => 'go1',
                    ],
                ] + Service::urls(['queue', 'user', 'mail', 'portal', 'rules', 'currency', 'lo', 'sms', 'graphin', 's3'], 'qa')
            );
    }
}
