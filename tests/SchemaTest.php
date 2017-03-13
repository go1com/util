<?php

namespace go1\util\schema\tests;

use Doctrine\DBAL\DriverManager;
use go1\util\schema\InstallTrait;
use PHPUnit_Framework_TestCase;

class SchemaTest extends PHPUnit_Framework_TestCase
{
    use InstallTrait;

    public function test()
    {
        $db = DriverManager::getConnection(['url' => 'sqlite://sqlite::memory:']);
        $this->installGo1Schema($db);

        $expectingTables = [
            'gc_domain', 'gc_enrolment', 'gc_flood', 'gc_instance', 'gc_kv',
            'gc_lo', 'gc_event', 'gc_lo_pricing', 'gc_tag',
            'gc_enrolment',
            'gc_ro', 'gc_role', 'gc_role', 'gc_tag', 'gc_user',
        ];

        $actualTables = $db->getSchemaManager()->listTableNames();
        foreach ($expectingTables as $table) {
            $this->assertTrue(in_array($table, $actualTables), "Table {$table} is created.");
        }
    }
}
