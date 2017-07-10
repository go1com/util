<?php

namespace go1\util\tests\model;

use go1\util\contract\ContractHelper;
use go1\util\Currency;
use go1\util\model\Contract;
use go1\util\portal\PortalPricing;
use go1\util\schema\mock\ContractMockTrait;
use go1\util\schema\mock\InstanceMockTrait;
use go1\util\schema\mock\UserMockTrait;
use go1\util\tests\UtilTestCase;

class ContractModelTest extends UtilTestCase
{
    use InstanceMockTrait;
    use UserMockTrait;
    use ContractMockTrait;

    private $instanceId;
    private $contractId;

    public function setUp()
    {
        parent::setUp();

        $mail = 'author@portal.com';
        $this->instanceId = $this->createInstance($this->db, ['data' => [
            'author'    => $mail,
            "user_plan" => [
                "license"   => 100,
                "product"   => "platform",
                "regional"  => "AU",
                "price"     => 1200,
                "currency"  => Currency::DEFAULT,
                "trial"     => PortalPricing::PLAN_STATUS_TRIAL,
                "expire"    => 0
            ]
        ]]);
        $this->contractId = $this->createContract($this->db, [
            'instance_id'       => $this->instanceId,
            'user_id'           => 100,
            'number_users'      => 100,
            'price'             => 1200,
            'tax'               => 120,
        ]);
    }

    public function testFormat()
    {
        $contract = ContractHelper::load($this->db, $this->contractId);
        $this->assertEquals(1200.0, $contract->getPrice());
        $this->assertEquals(120.0, $contract->getTax());

        $date = \DateTime::createFromFormat('Y-m-d\TH:i:sO', $contract->getStartDate());
        $this->assertEquals($date->format('Y-m-d\TH:i:sO'), $contract->getStartDate());
    }

    public function testGetUpdatedValues()
    {
        $contract = ContractHelper::load($this->db, $this->contractId);
        $originContract = unserialize(serialize($contract));
        $contract->set('status', Contract::STATUS_INACTIVE);
        $updatedValues = $contract->getUpdatedValues($originContract);

        $this->assertEquals(Contract::STATUS_INACTIVE, $updatedValues['status']);
        $this->assertTrue(!empty($updatedValues['updated']));
    }
}
