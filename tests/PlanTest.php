<?php

namespace go1\util\tests;

use go1\util\plan\Plan;
use go1\util\plan\PlanRepository;
use go1\util\plan\PlanStatuses;

class PlanTest extends UtilTestCase
{
    public function testCreate()
    {
        $repository = new PlanRepository($this->db, $this->queue);

        $input = Plan::create($raw = (object)[
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

        $id = $repository->create($input);
        $plan = $repository->load($id);

        $this->assertEquals($id, $plan->id);
        $this->assertEquals($raw->user_id, $plan->userId);
        $this->assertEquals($raw->assigner_id, $plan->assignerId);
        $this->assertEquals($raw->instance_id, $plan->instanceId);
        $this->assertEquals($raw->entity_type, $plan->entityType);
        $this->assertEquals($raw->entity_id, $plan->entityId);
        $this->assertEquals($raw->status, $plan->status);
        $this->assertNotContains('<script', $plan->data->note);

        return $plan;
    }

    public function testUpdate()
    {
        $repository = new PlanRepository($this->db, $this->queue);

        // Create the plan
        $input = Plan::create($raw = (object)[
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

        $id = $repository->create($input);
        $original = $repository->load($id);

        // Make update
        $new = clone $original;
        $new->status = PlanStatuses::LATE;
        $new->data = (object) ['note' => 'OK GO1, I am studying here!'];
        $repository->update($original, $new);

        // Load & check.
        $plan = $repository->load($original->id);
        $this->assertEquals(PlanStatuses::LATE, $plan->status);
        $this->assertEquals('OK GO1, I am studying here!', $plan->data->note);
    }

    public function testDelete()
    {
        $repository = new PlanRepository($this->db, $this->queue);

        // Create the plan
        $plan = Plan::create($raw = (object)[
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
        $repository->create($plan);

        // Delete it
        $repository->delete($plan->id);

        // Check
        $this->assertNotEmpty(true, is_numeric($plan->id));
        $this->assertEmpty($repository->load($plan->id));
    }

    public function testLoadMultiple()
    {
        $repository = new PlanRepository($this->db, $this->queue);

        $input = Plan::create($raw = (object)[
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

        $id1 = $repository->create($input);
        $id2 = $repository->create($input);

        $plans = $repository->loadMultiple([$id1, $id2]);

        $this->assertCount(2, $plans, 'Found 2 plan');

        return $plans;
    }

    public function testMerge()
    {
        $repository = new PlanRepository($this->db, $this->queue);

        $input = Plan::create($raw = (object)[
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

        $id = $repository->merge($input);
        $plan = $repository->load($id);

        $this->assertEquals($id, $plan->id);

        // merge with same data
        $id = $repository->merge($plan);

        $this->assertEquals($id, $plan->id);
        $this->assertEmpty($repository->loadRevisions($id));

        // merge with different data
        $plan->due = null;

        $id = $repository->merge($plan);

        $this->assertEquals($id, $plan->id);
        $this->assertCount(1, $repository->loadRevisions($id));
    }
}
