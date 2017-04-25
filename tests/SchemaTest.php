<?php

namespace go1\util\schema\tests;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\Schema;
use go1\util\DB;
use go1\util\schema\ActivitySchema;
use go1\util\schema\EnrolmentSchema;
use go1\util\schema\InstallTrait;
use PHPUnit\Framework\TestCase;

class SchemaTest extends TestCase
{
    use InstallTrait;

    public function test()
    {
        $db = DriverManager::getConnection(['url' => 'sqlite://sqlite::memory:']);
        $this->installGo1Schema($db);

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
}
