<?php

namespace go1\util\tests\customer;

use go1\util\customer\CustomerStatuses;
use go1\util\tests\UtilTestCase;

class CustomerStatusTest extends UtilTestCase
{
    public function test()
    {
        $this->assertEquals('Proposal', CustomerStatuses::PROPOSAL);
        $this->assertEquals('Onboarding', CustomerStatuses::ONBOARDING);
        $this->assertEquals('Live', CustomerStatuses::LIVE);
        $this->assertEquals('Cancelled', CustomerStatuses::CANCELLED);
        $this->assertEquals('Suspended', CustomerStatuses::SUSPENDED);
        $this->assertCount(5, CustomerStatuses::all());
    }
}
