<?php

namespace go1\util\tests\model;

use DateTime;
use go1\util\contract\ContractHelper;
use go1\util\schema\mock\ContractMockTrait;
use go1\util\tests\UtilTestCase;

class ContractModelTest extends UtilTestCase
{
    use ContractMockTrait;

    private function createStartDateContract(string $startDate, string $initialTerm)
    {
        return $this->createContract($this->db, [
            'instance_id'       => 1000,
            'user_id'           => 2000,
            'number_users'      => 100,
            'price'             => 1200,
            'initial_term'      => $initialTerm,
            'start_date'        => (new DateTime($startDate))->format('Y-m-d')
        ]);
    }

    /**
     * Start date < today < start date + initial term
     */
    public function testLessThan2Years()
    {
        $contractId = $this->createStartDateContract('-100 days', '2 years');
        $contract = ContractHelper::load($this->db, $contractId);
        $renewalDate = (new DateTime($contract->getRenewalDate()))->format('Y-m-d');

        $expectedRenewalDate = (new DateTime('-100 days'));
        $expectedRenewalDate->modify('+2 years');
        $this->assertEquals($expectedRenewalDate->format('Y-m-d'), $renewalDate);
    }

    public function testLessThan2Months()
    {
        $contractId = $this->createStartDateContract('-20 days', '2 months');
        $contract = ContractHelper::load($this->db, $contractId);
        $renewalDate = (new DateTime($contract->getRenewalDate()))->format('Y-m-d');

        $expectedRenewalDate = (new DateTime('-20 days'));
        $expectedRenewalDate->modify('+2 months');
        $this->assertEquals($expectedRenewalDate->format('Y-m-d'), $renewalDate);
    }

    /**
     * start date + initial term < today < start date + (2*initial term)
     */
    public function testGreaterThan2YearsAndLessThan4Years()
    {
        $contractId = $this->createStartDateContract('-3 years', '2 years');
        $contract = ContractHelper::load($this->db, $contractId);
        $renewalDate = (new DateTime($contract->getRenewalDate()))->format('Y-m-d');

        $expectedRenewalDate = (new DateTime('-3 years'));
        $expectedRenewalDate->modify('+4 years');
        $this->assertEquals($expectedRenewalDate->format('Y-m-d'), $renewalDate);
    }

    public function testGreaterThan1MonthAndLessThan2Months()
    {
        $contractId = $this->createStartDateContract('-40 days', '2 months');
        $contract = ContractHelper::load($this->db, $contractId);
        $renewalDate = (new DateTime($contract->getRenewalDate()))->format('Y-m-d');

        $expectedRenewalDate = (new DateTime('-40 days'));
        $expectedRenewalDate->modify('+2 months');
        $this->assertEquals($expectedRenewalDate->format('Y-m-d'), $renewalDate);
    }
}
