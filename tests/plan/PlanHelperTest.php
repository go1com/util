<?php

namespace go1\util\tests\plan;

use go1\util\plan\PlanHelper;
use go1\util\plan\PlanStatuses;
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
        $plan = PlanHelper::loadByEntityAndUser($this->db, $this->entityType, $this->entityId, $this->userId);
        $this->assertFalse($plan);

        $this->createPlan($this->db, ['entity_type' => $this->entityType, 'entity_id' => $this->entityId, 'user_id' => $this->userId, 'status' => PlanStatuses::EXPIRED]);
        $plan = PlanHelper::loadByEntityAndUser($this->db, $this->entityType, $this->entityId, $this->userId);
        $this->assertFalse($plan);

        $this->createPlan($this->db, ['entity_type' => $this->entityType, 'entity_id' => $this->entityId, 'user_id' => $this->userId]);
        $plan = PlanHelper::loadByEntityAndUser($this->db, $this->entityType, $this->entityId, $this->userId);
        $this->assertNotFalse($plan);
    }

    public function testPlanIds()
    {
        $plan = PlanHelper::userPlanIds($this->db, $this->entityType, $this->userId);
        $this->assertEquals([], $plan);

        $this->createPlan($this->db, ['entity_type' => $this->entityType, 'entity_id' => $this->entityId, 'user_id' => $this->userId, 'status' => PlanStatuses::EXPIRED]);
        $plan = PlanHelper::userPlanIds($this->db, $this->entityType, $this->userId);
        $this->assertEquals([], $plan);

        $plan1Id = $this->createPlan($this->db, ['entity_type' => $this->entityType, 'entity_id' => $this->entityId, 'user_id' => $this->userId, 'status' => PlanStatuses::PENDING]);
        $plan2Id = $this->createPlan($this->db, ['entity_type' => $this->entityType, 'entity_id' => $this->entityId + 1, 'user_id' => $this->userId, 'status' => PlanStatuses::PENDING]);
        $plan3Id = $this->createPlan($this->db, ['entity_type' => $this->entityType, 'entity_id' => $this->entityId + 2, 'user_id' => $this->userId, 'status' => PlanStatuses::ASSIGNED]);
        $plans = PlanHelper::userPlanIds($this->db, $this->entityType, $this->userId, PlanStatuses::PENDING);
        $this->assertEquals(2, count($plans));
        $this->assertEquals($plan1Id, $plans[0]);
        $this->assertEquals($plan2Id, $plans[1]);
    }
}
