<?php

namespace go1\util\schema\tests;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\Schema;
use go1\util\DB;
use go1\util\schema\ActivitySchema;
use go1\util\schema\ContractSchema;
use go1\util\schema\EnrolmentSchema;
use go1\util\schema\InstallTrait;
use go1\util\schema\mock\UserMockTrait;
use go1\util\schema\MailSchema;
use PHPUnit\Framework\TestCase;

class SchemaTest extends TestCase
{
    use InstallTrait;
    use UserMockTrait;

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

    public function testMail()
    {
        $db = DriverManager::getConnection(['url' => 'sqlite://sqlite::memory:']);

        DB::install($db, [
            function (Schema $schema) {
                MailSchema::install($schema);
            },
        ]);

        $schema = $db->getSchemaManager()->createSchema();
        $log = $schema->getTable('mail_log');
        $this->assertEquals(true, $log->hasColumn('id'));
        $this->assertEquals(true, $log->hasColumn('smtp_id'));
        $this->assertEquals(true, $log->hasColumn('category'));
    }
}
