<?php

namespace go1\util\tests;

use Doctrine\DBAL\DriverManager;
use go1\util\schema\InstallTrait;
use go1\util\schema\mock\UserMockTrait;
use PHPUnit_Framework_TestCase;

abstract class UtilTestCase extends PHPUnit_Framework_TestCase
{
    use InstallTrait;
    use UserMockTrait;
    protected $db;

    public function setUp()
    {
        $this->db = DriverManager::getConnection(
            ['url' => 'sqlite://sqlite::memory:']
        );
        $this->installGo1Schema($this->db);
    }

}