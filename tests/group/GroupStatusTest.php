<?php

namespace go1\util\tests\group;

use go1\util\group\GroupStatus;
use go1\util\tests\UtilTestCase;

class GroupStatusTest extends UtilTestCase
{
    public function test()
    {
        $this->assertEquals(0, GroupStatus::PRIVATE);
        $this->assertEquals(1, GroupStatus::PUBLIC);
        $this->assertEquals(2, GroupStatus::LOCKED);
    }
}
