<?php

namespace go1\util\tests\portal;

use go1\util\portal\PortalChecker;
use go1\util\schema\mock\InstanceMockTrait;
use go1\util\tests\UtilTestCase;

class PortalCheckerTest extends UtilTestCase
{
    use InstanceMockTrait;

    private $instanceId;
    /** @var  PortalChecker */
    private $portalChecker;

    public function setUp()
    {
        parent::setUp();

        $this->portalChecker = new PortalChecker();

        $data = [
            'user_plan' => [
                'license' => 50,
            ],
        ];
        $this->instanceId = $this->createInstance($this->db, ['data' => $data]);
    }
}
