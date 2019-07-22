<?php

namespace go1\util\tests;

use go1\util\edge\event_publishing\LoSaveAssessorEventEmbedder;
use go1\util\lo\LoTypes;
use go1\util\schema\mock\LoMockTrait;
use go1\util\schema\mock\PortalMockTrait;

class LoSaveAssessorEventEmbedderTest extends UtilCoreTestCase
{
    use PortalMockTrait;
    use LoMockTrait;

    protected $expectLo = [
        'id'          => 1,
        'type'        => LoTypes::COURSE,
        'instance_id' => 1,
    ];

    public function test()
    {
        $embedder = new LoSaveAssessorEventEmbedder($this->go1);
        $portalId = $this->createPortal($this->go1, ['title' => 'ngoc.mygo1.com']);
        $courseId = $this->createCourse($this->go1, ['instance_id' => $portalId]);
        $embedded = $embedder->embedded((object)['id' => $courseId]);
        $this->assertArrayHasKey('lo', $embedded);
        $this->assertEmpty(array_diff_assoc($this->expectLo, (array)$embedded['lo']));
    }
}
