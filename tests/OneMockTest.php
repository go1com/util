<?php

namespace go1\util\schema\tests;

use Doctrine\DBAL\DriverManager;
use go1\util\schema\mock\OneMockTrait;
use PHPUnit\Framework\TestCase;

class OneMockTest extends TestCase
{
    public function test()
    {
        $one = new class
        {
            use OneMockTrait;
        };

        $one->install($db = DriverManager::getConnection(['url' => 'sqlite://sqlite::memory:']));

        // Check that we have something in database.
        $this->assertGreaterThanOrEqual(2, $db->fetchColumn('SELECT COUNT(*) FROM gc_instance'));
        $this->assertGreaterThanOrEqual(6, $db->fetchColumn('SELECT COUNT(*) FROM gc_role'));
        $this->assertGreaterThanOrEqual(6, $db->fetchColumn('SELECT COUNT(*) FROM gc_user'));
        $this->assertGreaterThanOrEqual(14, $db->fetchColumn('SELECT COUNT(*) FROM gc_ro'));
        $this->assertGreaterThanOrEqual(9, $db->fetchColumn('SELECT COUNT(*) FROM gc_lo'));
        $this->assertGreaterThanOrEqual(9, $db->fetchColumn('SELECT COUNT(*) FROM gc_enrolment'));
    }
}
