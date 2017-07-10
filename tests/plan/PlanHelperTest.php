<?php

namespace go1\util\tests\plan;

use go1\util\plan\Plan;
use go1\util\plan\PlanHelper;
use go1\util\schema\mock\PlanMockTrait;
use go1\util\tests\UtilTestCase;

class PlanHelperTest extends UtilTestCase
{
    use PlanMockTrait;

    protected $entityType = 'award';
    protected $entityId   = 111;
    protected $userId     = 222;

    public function testLoadByEntityAndUser()
    {
        $plan = PlanHelper::loadByEntityUserAndStatus($this->db, $this->entityType, $this->entityId, $this->userId);
        $this->assertFalse($plan);

        $this->createPlan($this->db, ['entity_type' => $this->entityType, 'entity_id' => $this->entityId, 'user_id' => $this->userId, 'status' => Plan::STATUS_EXPIRED]);
        $plan = PlanHelper::loadByEntityUserAndStatus($this->db, $this->entityType, $this->entityId, $this->userId);
        $this->assertFalse($plan);

        $this->createPlan($this->db, ['entity_type' => $this->entityType, 'entity_id' => $this->entityId, 'user_id' => $this->userId]);
        $plan = PlanHelper::loadByEntityUserAndStatus($this->db, $this->entityType, $this->entityId, $this->userId);
        $this->assertNotFalse($plan);
    }
}
