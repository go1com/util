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
use PHPUnit\Framework\TestCase;

class SchemaTest extends TestCase
{
    use InstallTrait;
    use UserMockTrait;

    public function test()
    {
        $db = DriverManager::getConnection(['url' => 'sqlite://sqlite::memory:']);
        $this->installGo1Schema($db, true, $accountsName = 'accounts.qa');

        $expectingTables = [
            'gc_domain', 'gc_enrolment', 'gc_instance',
            'gc_lo', 'gc_event', 'gc_lo_pricing', 'gc_tag',
            'gc_plan', 'gc_enrolment',
            'gc_ro', 'gc_role', 'gc_role', 'gc_tag', 'gc_user',
        ];

        $actualTables = $db->getSchemaManager()->listTableNames();
        foreach ($expectingTables as $table) {
            $this->assertTrue(in_array($table, $actualTables), "Table {$table} is created.");
        }

        # Check DB user views.
        $this->createUser($db, ['instance' => $accountsName]);
        $this->createUser($db, ['instance' => 'qa.mygo1.com']);
        $this->assertEquals(1, $db->fetchColumn('SELECT COUNT(*) FROM gc_users WHERE 1'));
        $this->assertEquals(1, $db->fetchColumn('SELECT COUNT(*) FROM gc_accounts WHERE 1'));
    }

    public function testEnrolment()
    {
        $db = DriverManager::getConnection(['url' => 'sqlite://sqlite::memory:']);

        DB::install($db, [
            function (Schema $schema) {
                EnrolmentSchema::installManualRecord($schema);
            },
        ]);

        $schema = $db->getSchemaManager()->createSchema();
        $manual = $schema->getTable('enrolment_manual');
        $this->assertEquals(true, $manual->hasColumn('id'));
    }

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
