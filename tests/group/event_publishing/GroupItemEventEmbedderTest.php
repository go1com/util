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
        $embedder = new GroupItemEventEmbedder($this->db, $this->db, $this->db);
        $portalId = $this->createPortal($this->db, ['title' => 'ngoc.mygo1.com']);
        $courseId = $this->createCourse($this->db, ['instance_id' => $portalId]);
        $id = $this->createGroup($this->db, ['instance_id' => $portalId]);
        $this->createGroupItem($this->db, ['group_id' => $id, 'entity_type' => GroupItemTypes::LO, 'entity_id' => $courseId]);

        $groupItem = $this->db
            ->executeQuery('SELECT * FROM social_group_item WHERE id = ?', [$id], [DB::STRING])
            ->fetch(DB::OBJ);

        $embedded = $embedder->embedded($groupItem);

        $this->assertArrayHasKey('entity', $embedded);
        $this->assertArrayHasKey('group', $embedded);
        $this->assertArraySubset($this->expectLo, (array)$embedded['entity']);
    }
}
