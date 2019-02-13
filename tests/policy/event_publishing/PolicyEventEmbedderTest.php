<?php

namespace go1\util\tests\policy;

use go1\util\DB;
use go1\util\EntityTypes;
use go1\util\policy\event_publishing\PolicyEventEmbedder;
use go1\util\policy\Realm;
use go1\util\schema\mock\LoMockTrait;
use go1\util\schema\mock\PolicyMockTrait;
use go1\util\schema\mock\PortalMockTrait;
use go1\util\tests\UtilTestCase;

class PolicyEventEmbedderTest extends UtilTestCase
{
    use PolicyMockTrait;
    use PortalMockTrait;
    use LoMockTrait;

    protected $expectLo = [
        'id'          => 1,
        'type'        => 'course',
        'instance_id' => 1,
    ];

    public function test()
    {
        $embedder = new PolicyEventEmbedder($this->go1, $this->go1);
        $portalId = $this->createPortal($this->go1, ['title' => 'ngoc.mygo1.com']);
        $courseId = $this->createCourse($this->go1, ['instance_id' => $portalId]);
        $accountId = $this->createUser($this->go1, ['instance' => 'ngoc.mygo1.com']);
        $id = $this->createItem(
            $this->go1,
            [
                'type'             => Realm::ACCESS,
                'portal_id'        => $portalId,
                'host_entity_type' => EntityTypes::LO,
                'host_entity_id'   => $courseId,
                'entity_type'      => EntityTypes::USER,
                'entity_id'        => $accountId,
            ]
        );
        $policyItem = $this->go1
            ->executeQuery('SELECT * FROM policy_policy_item WHERE id = ?', [$id], [DB::STRING])
            ->fetch(DB::OBJ);

        $embedded = $embedder->embedded($policyItem);

        $this->assertArrayHasKey('hostEntity', $embedded);
        $hostEntity = (array)$embedded['hostEntity'];
        $this->assertEquals($this->expectLo['id'], $hostEntity['id']);
        $this->assertEquals($this->expectLo['type'], $hostEntity['type']);
        $this->assertEquals($this->expectLo['instance_id'], $hostEntity['instance_id']);
        $this->assertEquals($accountId, $embedded['entity']->id);
    }
}
