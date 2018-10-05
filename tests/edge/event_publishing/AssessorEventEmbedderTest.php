<?php

namespace go1\util\tests;

use go1\util\edge\event_publishing\AssessorEventEmbedder;
use go1\util\lo\LoTypes;
use go1\util\schema\mock\LoMockTrait;
use go1\util\schema\mock\PortalMockTrait;
use go1\util\DB;
use go1\util\edge\EdgeHelper;
use go1\util\edge\EdgeTypes;

class AssessorEventEmbedderTest extends UtilCoreTestCase
{
    use PortalMockTrait;
    use LoMockTrait;

    protected $edgeIds;
    protected $expectLo = [
        'id'          => 1,
        'type'        => LoTypes::COURSE,
        'instance_id' => 1,
    ];

    public function test()
    {
        $embedder = new AssessorEventEmbedder($this->db);
        $portalId = $this->createPortal($this->db, ['title' => 'ngoc.mygo1.com']);
        $courseId = $this->createCourse($this->db, ['instance_id' => $portalId]);
        $id = EdgeHelper::link($this->db, $this->queue, EdgeTypes::COURSE_ASSESSOR, $courseId, $userId = 2, $weight = 0);

        $edge = $this->db
            ->executeQuery('SELECT * FROM gc_ro WHERE id = ?', [$id], [DB::STRING])
            ->fetch(DB::OBJ);

        $embedded = $embedder->embedded($edge);

        $this->assertArrayHasKey('lo', $embedded);
        $this->assertArraySubset($this->expectLo, (array)$embedded['lo']);
    }
}
