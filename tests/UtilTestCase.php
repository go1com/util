<?php

namespace go1\util\tests;

use Doctrine\DBAL\Schema\Schema;
use go1\clients\tests\UtilCoreClientsTestCase;
use go1\util\plan\PlanRepository;
use go1\util\schema\AssignmentSchema;
use go1\util\schema\AwardSchema;
use go1\util\schema\CreditSchema;
use go1\util\schema\EckSchema;
use go1\util\schema\PolicySchema;
use go1\util\schema\QuizSchema;
use go1\util\task\TaskSchema;
use go1\util\UtilServiceProvider;
use Pimple\Container;

abstract class UtilTestCase extends UtilCoreClientsTestCase
{
    protected $taskService;
    protected $schemaClasses = [
        AwardSchema::class,
        AssignmentSchema::class,
        CreditSchema::class,
        EckSchema::class,
        PlanRepository::class,
        QuizSchema::class,
        PolicySchema::class,
    ];

    public function setupContainer(Container &$container)
    {
        parent::setupContainer($container);

        $container->register(new UtilServiceProvider);
    }

    protected function setupDatabaseSchema(Schema $schema)
    {
        foreach ($this->schemaClasses as $schemaClass) {
            call_user_func([$schemaClass, 'install'], $schema);
        }

        if ($this->taskService) {
            TaskSchema::install($schema, $this->taskService);
        }
    }
}
