<?php

namespace go1\util\tests\plan;

use go1\util\plan\event_publishing\PlanCreateEventEmbedder;
use go1\util\plan\event_publishing\PlanDeleteEventEmbedder;
use go1\util\plan\event_publishing\PlanUpdateEventEmbedder;
use go1\util\plan\Plan;
use go1\util\plan\PlanRepository;
use go1\util\plan\PlanStatuses;
use go1\util\tests\UtilCoreTestCase;

class PlanTest extends UtilCoreTestCase
{
    /**
     * @var PlanRepository
     */
    private $rPlan;

    public function setUp() : void
    {
        parent::setUp();

        $this->rPlan = new PlanRepository(
            $this->go1,
            $this->queue,
            new PlanCreateEventEmbedder($this->go1),
            new PlanUpdateEventEmbedder($this->go1),
            new PlanDeleteEventEmbedder($this->go1)
        );
    }

    public function testCreate()
    {
        $input = Plan::create($raw = (object) [
            'user_id'      => 123,
            'assigner_id'  => 111,
            'instance_id'  => 124,
            'entity_type'  => 'lo',
            'entity_id'    => 555,
            'status'       => PlanStatuses::INTERESTING,
            'created_date' => time(),
            'due_date'     => '+ 2 months',
            'data'         => [
                'note' => 'Something cool! <script>alert(123);</script>',
            ],
        ]);

        $id = $this->rPlan->create($input);
        $plan = $this->rPlan->load($id);

        $this->assertEquals($id, $plan->id);
        $this->assertEquals($raw->user_id, $plan->userId);
        $this->assertEquals($raw->assigner_id, $plan->assignerId);
        $this->assertEquals($raw->instance_id, $plan->instanceId);
        $this->assertEquals($raw->entity_type, $plan->entityType);
        $this->assertEquals($raw->entity_id, $plan->entityId);
        $this->assertEquals($raw->status, $plan->status);
        $this->assertStringNotContainsString('<script', $plan->data->note);

        return $plan;
    }

    public function testUpdate()
    {
        // Create the plan
        $input = Plan::create($raw = (object) [
            'user_id'      => 123,
            'assigner_id'  => 111,
            'instance_id'  => 124,
            'entity_type'  => 'lo',
            'entity_id'    => 555,
            'status'       => PlanStatuses::INTERESTING,
            'created_date' => time(),
            'due_date'     => '+ 2 months',
            'data'         => ['note' => 'Something cool!',],
        ]);

        $id = $this->rPlan->create($input);
        $original = $this->rPlan->load($id);

        // Make update
        $new = clone $original;
        $new->status = PlanStatuses::LATE;
        $new->data = (object) ['note' => 'OK GO1, I am studying here!'];
        $this->rPlan->update($original, $new);

        // Load & check.
        $plan = $this->rPlan->load($original->id);
        $this->assertEquals(PlanStatuses::LATE, $plan->status);
        $this->assertEquals('OK GO1, I am studying here!', $plan->data->note);
    }

    public function testDelete()
    {
        // Create the plan
        $plan = Plan::create($raw = (object) [
            'user_id'      => 123,
            'assigner_id'  => 111,
            'instance_id'  => 124,
            'entity_type'  => 'lo',
            'entity_id'    => 555,
            'status'       => PlanStatuses::INTERESTING,
            'created_date' => time(),
            'due_date'     => '+ 2 months',
            'data'         => ['note' => 'Something cool!',],
        ]);
        $this->rPlan->create($plan);

        // Delete it
        $this->rPlan->delete($plan->id);

        // Check
        $this->assertNotEmpty(true, is_numeric($plan->id));
        $this->assertEmpty($this->rPlan->load($plan->id));
    }

    public function testLoadMultiple()
    {
        $input = Plan::create($raw = (object) [
            'user_id'      => 123,
            'assigner_id'  => 111,
            'instance_id'  => 124,
            'entity_type'  => 'lo',
            'entity_id'    => 555,
            'status'       => PlanStatuses::INTERESTING,
            'created_date' => time(),
            'due_date'     => '+ 2 months',
            'data'         => [
                'note' => 'Something cool! <script>alert(123);</script>',
            ],
        ]);

        $id1 = $this->rPlan->create($input);
        $id2 = $this->rPlan->create($input);

        $plans = $this->rPlan->loadMultiple([$id1, $id2]);

        $this->assertCount(2, $plans, 'Found 2 plan');

        return $plans;
    }

    public function testMerge()
    {
        $input = Plan::create($raw = (object) [
            'user_id'      => 123,
            'assigner_id'  => 111,
            'instance_id'  => 124,
            'entity_type'  => 'lo',
            'entity_id'    => 555,
            'status'       => PlanStatuses::INTERESTING,
            'created_date' => time(),
            'due_date'     => '+ 2 months',
            'data'         => [
                'note' => 'Something cool! <script>alert(123);</script>',
            ],
        ]);

        $id = $this->rPlan->merge($input);
        $plan = $this->rPlan->load($id);

        $this->assertEquals($id, $plan->id);

        // merge with same data
        $id = $this->rPlan->merge($plan);

        $this->assertEquals($id, $plan->id);
        $this->assertEmpty($this->rPlan->loadRevisions($id));

        // merge with different data
        $plan->due = null;

        $id = $this->rPlan->merge($plan);

        $this->assertEquals($id, $plan->id);
        $this->assertCount(1, $this->rPlan->loadRevisions($id));
    }
}
