<?php

namespace go1\util\schema\tests;

use Doctrine\DBAL\DriverManager;
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
}
