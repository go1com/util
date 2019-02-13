<?php

namespace go1\util\tests;

use go1\util\group\event_publishing\GroupItemEventEmbedder;
use go1\util\schema\mock\LoMockTrait;
use go1\util\schema\mock\PortalMockTrait;
use go1\util\schema\mock\GroupMockTrait;
use go1\util\group\GroupItemTypes;
use go1\util\DB;

class GroupItemEventEmbedderTest extends UtilTestCase
{
    use GroupMockTrait;
    use PortalMockTrait;
    use LoMockTrait;

    protected $edgeIds;
    protected $expectLo = [
        'id'          => 1,
        'type'        => 'course',
        'instance_id' => 1,
    ];

    public function test()
    {
        $embedder = new GroupItemEventEmbedder($this->go1, $this->go1, $this->go1);
        $portalId = $this->createPortal($this->go1, ['title' => 'ngoc.mygo1.com']);
        $courseId = $this->createCourse($this->go1, ['instance_id' => $portalId]);
        $id = $this->createGroup($this->go1, ['instance_id' => $portalId]);
        $this->createGroupItem($this->go1, ['group_id' => $id, 'entity_type' => GroupItemTypes::LO, 'entity_id' => $courseId]);

        $groupItem = $this->go1
            ->executeQuery('SELECT * FROM social_group_item WHERE id = ?', [$id], [DB::INTEGER])
            ->fetch(DB::OBJ);

        $embedded = $embedder->embedded($groupItem);

        $this->assertArrayHasKey('entity', $embedded);
        $this->assertArrayHasKey('group', $embedded);
        $this->assertArrayHasKey('portal', $embedded);

        $entity = (array)$embedded['entity'];
        $this->assertEquals($this->expectLo['id'], $entity['id']);
        $this->assertEquals($this->expectLo['type'], $entity['type']);
        $this->assertEquals($this->expectLo['instance_id'], $entity['instance_id']);
    }
}
