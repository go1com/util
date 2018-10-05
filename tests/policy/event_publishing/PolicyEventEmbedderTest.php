<?php

namespace go1\util\tests\policy;

use go1\util\policy\event_publishing\PolicyEventEmbedder;
use go1\util\tests\UtilTestCase;
use go1\util\schema\mock\LoMockTrait;
use go1\util\schema\mock\PortalMockTrait;
use go1\util\schema\mock\PolicyMockTrait;
use go1\util\policy\Realm;
use go1\util\EntityTypes;
use go1\util\DB;

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
        $embedder = new PolicyEventEmbedder($this->db);
        $portalId = $this->createPortal($this->db, ['title' => 'ngoc.mygo1.com']);
        $courseId = $this->createCourse($this->db, ['instance_id' => $portalId]);
        $id = $this->createItem(
            $this->db,
            [
                'type'             => Realm::ACCESS,
                'portal_id'        => $portalId,
                'host_entity_type' => EntityTypes::LO,
                'host_entity_id'   => $courseId,
                'entity_type'      => EntityTypes::USER,
                'entity_id'        => $accountId = 14,
            ]
        );
        $policyItem = $this->db
            ->executeQuery('SELECT * FROM policy_policy_item WHERE id = ?', [$id], [DB::STRING])
            ->fetch(DB::OBJ);

        $embedded = $embedder->embedded($policyItem);

        $this->assertArrayHasKey('hostEntity', $embedded);
        $this->assertArraySubset($this->expectLo, (array)$embedded['hostEntity']);
    }
}
