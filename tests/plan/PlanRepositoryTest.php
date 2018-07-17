<?php

namespace go1\util\tests\plan;

use go1\util\plan\Plan;
use go1\util\plan\PlanHelper;
use go1\util\plan\PlanRepository;
use go1\util\plan\PlanStatuses;
use go1\util\plan\PlanTypes;
use go1\util\schema\mock\PlanMockTrait;
use go1\util\tests\UtilTestCase;

class PlanRepositoryTest extends UtilTestCase
{
    use PlanMockTrait;

    protected $entityType = 'award';
    protected $entityId   = 111;
    protected $userId     = 222;
    protected $repo;

    public function setUp()
    {
        parent::setUp();
        $this->repo = new PlanRepository($this->db, $this->queue);
    }

    public function testLoadSuggestedPlan()
    {
        $plan = $this->repo->loadSuggestedPlan($this->entityType, $this->entityId, $this->userId);
        $this->assertNull($plan);

        $this->createPlan($this->db, ['entity_type' => $this->entityType, 'entity_id' => $this->entityId, 'user_id' => $this->userId, 'type' => PlanTypes::SUGGESTED]);
        $plan = $this->repo->loadSuggestedPlan($this->entityType, $this->entityId, $this->userId);
        $this->assertEquals($plan->entityType, $this->entityType);
        $this->assertTrue($plan instanceof Plan);
    }
}
