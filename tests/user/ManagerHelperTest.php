<?php

namespace go1\util\tests\user;

use go1\util\tests\UtilTestCase;
use go1\util\user\ManagerHelper;

class ManagerHelperTest extends UtilTestCase
{
    public function test()
    {
        $this->queue;

        $userId = 10;
        $managerId = 20;
        $instanceId = 30;
        ManagerHelper::link($this->db, $this->queue, $instanceId, $managerId, $userId);

        $this->assertEquals(true, ManagerHelper::isManagerOf($this->db, $instanceId, $managerId, $userId));
        $this->assertEquals(false, ManagerHelper::isManagerOf($this->db, $instanceId + 5, $managerId, $userId));
        $this->assertEquals(false, ManagerHelper::isManagerOf($this->db, $instanceId, $managerId + 5, $userId));
        $this->assertEquals(false, ManagerHelper::isManagerOf($this->db, $instanceId, $managerId, $userId + 5));
    }
}
