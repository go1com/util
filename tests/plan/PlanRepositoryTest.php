<?php

namespace go1\util\tests\plan;

use go1\clients\MqClient;
use go1\util\plan\event_publishing\PlanCreateEventEmbedder;
use go1\util\plan\event_publishing\PlanDeleteEventEmbedder;
use go1\util\plan\event_publishing\PlanUpdateEventEmbedder;
use go1\util\plan\Plan;
use go1\util\plan\PlanRepository;
use go1\util\plan\PlanStatuses;
use go1\util\plan\PlanTypes;
use go1\util\queue\Queue;
use go1\util\schema\mock\PlanMockTrait;
use go1\util\tests\UtilCoreTestCase;

class PlanRepositoryTest extends UtilCoreTestCase
{
    use PlanMockTrait;

    protected $entityType = 'award';
    protected $entityId   = 111;
    protected $userId     = 222;
    protected $assignerId = 333;
    protected $portalId   = 444;
    protected $rPlan;

    /** @var MqClient */
    protected $queue;

    public function setUp()
    {
        parent::setUp();

        $this->rPlan = new PlanRepository(
            $this->db,
            $this->queue,
            new PlanCreateEventEmbedder($this->db),
            new PlanUpdateEventEmbedder($this->db),
            new PlanDeleteEventEmbedder($this->db)
        );
    }

    public function testLoadSuggestedPlan()
    {
        $plan = $this->rPlan->loadSuggestedPlan($this->entityType, $this->entityId, $this->userId);
        $this->assertNull($plan);

        $this->createPlan($this->db, ['entity_type' => $this->entityType, 'entity_id' => $this->entityId, 'user_id' => $this->userId, 'type' => PlanTypes::SUGGESTED]);
        $plan = $this->rPlan->loadSuggestedPlan($this->entityType, $this->entityId, $this->userId);
        $this->assertEquals($plan->entityType, $this->entityType);
        $this->assertTrue($plan instanceof Plan);
    }

    public function planNotifyStatus()
    {
        return [
            [false, ['notify' => true], true],
            [true, ['notify' => false], true],
            [false, [], false],
            [true, [], true]
        ];
    }
    /**
     * @dataProvider planNotifyStatus
     */
    public function testCreatePlanNotify($notifyStatus, $dataContext, $expectedNotify)
    {
        $plan = Plan::create((object) [
            'instance_id' => $this->portalId,
            'entity_type' => $this->entityType,
            'entity_id'   => $this->entityId,
            'user_id'     => $this->userId,
            'assigner_id' => $this->assignerId,
            'type'        => PlanTypes::SUGGESTED,
            'status'      => PlanStatuses::ASSIGNED,
        ]);
        $this->rPlan->create($plan, $notifyStatus, $dataContext);
        $msg = (object) $this->queueMessages[Queue::PLAN_CREATE][0];
        $this->assertEquals($msg->notify, $expectedNotify);
    }
}
