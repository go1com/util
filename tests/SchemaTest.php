<?php

namespace go1\util\tests;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\Schema;
use go1\util\DB;
use go1\util\schema\ActivitySchema;
use go1\util\schema\ContractSchema;
use PHPUnit\Framework\TestCase;

class SchemaTest extends TestCase
{
    public function testActivity()
    {
        $db = DriverManager::getConnection(['url' => 'sqlite://sqlite::memory:']);

        DB::install($db, [
            function (Schema $schema) {
                ActivitySchema::install($schema);
            },
        ]);

        $schema = $db->getSchemaManager()->createSchema();
        $manual = $schema->getTable('activity');
        $this->assertEquals(true, $manual->hasColumn('id'));
    }

    public function testContract()
    {
        $db = DriverManager::getConnection(['url' => 'sqlite://sqlite::memory:']);

        DB::install($db, [
            function (Schema $schema) {
                ContractSchema::install($schema);
            },
        ]);

        $schema = $db->getSchemaManager()->createSchema();
        $manual = $schema->getTable('contract');
        $this->assertEquals(true, $manual->hasColumn('id'));
    }
}
