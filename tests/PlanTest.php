<?php

namespace go1\util\tests;

use go1\util\plan\Plan;
use go1\util\plan\PlanRepository;

class PlanTest extends UtilTestCase
{
    public function testCreate()
    {
        $repository = new PlanRepository($this->db);

        $input = Plan::create($raw = (object) [
            'user_id'      => 123,
            'assigner_id'  => 111,
            'entity_type'  => 'lo',
            'entity_id'    => 555,
            'status'       => Plan::STATUS_INTERESTING,
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
        $this->assertEquals($raw->entity_type, $plan->entityType);
        $this->assertEquals($raw->entity_id, $plan->entityId);
        $this->assertEquals($raw->status, $plan->status);
        $this->assertNotContains('<script', $plan->data->note);
    }
}
