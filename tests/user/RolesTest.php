<?php

namespace go1\util\tests\user;

use go1\util\tests\UtilTestCase;
use go1\util\user\Roles;

class RolesTest extends UtilTestCase
{
    public function testRole()
    {
        $this->assertEquals(Roles::ADMIN, Roles::getRoleByName('Administrator'));
        $this->assertEquals(Roles::STUDENT, Roles::getRoleByName('Student'));
        $this->assertEquals(Roles::ASSESSOR, Roles::getRoleByName('Assessor'));
        $this->assertEquals(Roles::MANAGER, Roles::getRoleByName('Manager'));
        $this->assertFalse(Roles::getRoleByName('ROLE'));
    }
}
