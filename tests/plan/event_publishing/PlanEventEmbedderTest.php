<?php

namespace go1\util\tests\plan\event_publishing;

use go1\util\edge\EdgeTypes;
use go1\util\plan\event_publishing\PlanCreateEventEmbedder;
use go1\util\plan\event_publishing\PlanDeleteEventEmbedder;
use go1\util\plan\event_publishing\PlanUpdateEventEmbedder;
use go1\util\plan\Plan;
use go1\util\plan\PlanHelper;
use go1\util\schema\mock\LoMockTrait;
use go1\util\schema\mock\PlanMockTrait;
use go1\util\schema\mock\PortalMockTrait;
use go1\util\tests\UtilCoreTestCase;

class PlanEventEmbedderTest extends UtilCoreTestCase
{
    use PortalMockTrait;
    use LoMockTrait;
    use PlanMockTrait;

    protected $portalId;
    protected $userId;
    protected $accountId;
    protected $profileId = 999;
    protected $jwt;
    protected $courseId;
    protected $planId;

    public function setUp() : void
    {
        parent::setUp();

        $c = $this->getContainer();
        $this->portalId = $this->createPortal($this->go1, ['title' => 'qa.mygo1.com']);
        $this->userId = $this->createUser($this->go1, ['instance' => $c['accounts_name'], 'profile_id' => $this->profileId]);
        $this->accountId = $this->createUser($this->go1, ['instance' => 'qa.mygo1.com', 'profile_id' => $this->profileId]);
        $this->link($this->go1, EdgeTypes::HAS_ACCOUNT, $this->userId, $this->accountId);
        $this->jwt = $this->jwtForUser($this->go1, $this->userId, 'qa.mygo1.com');
        $this->courseId = $this->createCourse($this->go1, ['instance_id' => $this->portalId]);
        $this->planId = $this->createPlan($this->go1, [
            'user_id'     => $this->userId,
            'instance_id' => $this->portalId,
            'entity_id'   => $this->courseId,
        ]);
    }

    public function testPlanUpdateEventEmbedder()
    {
        $embedder = new PlanUpdateEventEmbedder($this->go1);
        $plan = PlanHelper::load($this->go1, $this->planId);
        $plan = Plan::create($plan);
        $embedded = $embedder->embedded($plan);

        $this->assertEquals('qa.mygo1.com', $embedded['portal']->title);
        $this->assertEquals($this->profileId, $embedded['account']->profile_id);
        $this->assertEquals($this->accountId, $embedded['account']->id);
        $this->assertEquals('course', $embedded['entity']->type);
        $this->assertEquals($this->courseId, $embedded['entity']->id);
        $this->assertEquals($this->portalId, $embedded['portal']->id);
    }

    public function testPlanCreateEventEmbedder()
    {
        $embedder = new PlanCreateEventEmbedder($this->go1);
        $plan = PlanHelper::load($this->go1, $this->planId);
        $plan = Plan::create($plan);
        $embedded = $embedder->embedded($plan);

        $this->assertEquals('qa.mygo1.com', $embedded['portal']->title);
        $this->assertEquals($this->profileId, $embedded['account']->profile_id);
        $this->assertEquals($this->accountId, $embedded['account']->id);
        $this->assertEquals('course', $embedded['entity']->type);
        $this->assertEquals($this->courseId, $embedded['entity']->id);
        $this->assertEquals($this->portalId, $embedded['portal']->id);
    }

    public function testPlanDeleteEventEmbedder()
    {
        $embedder = new PlanDeleteEventEmbedder($this->go1);
        $plan = PlanHelper::load($this->go1, $this->planId);
        $plan = Plan::create($plan);
        $embedded = $embedder->embedded($plan);

        $this->assertEquals('qa.mygo1.com', $embedded['portal']->title);
        $this->assertEquals($this->profileId, $embedded['account']->profile_id);
        $this->assertEquals($this->accountId, $embedded['account']->id);
        $this->assertEquals('course', $embedded['entity']->type);
        $this->assertEquals($this->courseId, $embedded['entity']->id);
        $this->assertEquals($this->portalId, $embedded['portal']->id);
    }
}
