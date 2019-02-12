<?php

namespace go1\util\tests\lo;

use go1\util\lo\event_publishing\LoCustomTagCreateEventEmbedder;
use go1\util\schema\mock\LoMockTrait;
use go1\util\schema\mock\PortalMockTrait;
use go1\util\tests\UtilCoreTestCase;

class LoCustomTagCreateEventEmbedderTest extends UtilCoreTestCase
{
    use PortalMockTrait;
    use LoMockTrait;

    protected $portalId;
    protected $userId;
    protected $accountId;
    protected $jwt;
    protected $courseId;
    protected $moduleId;
    protected $eventLiId;

    public function setUp() : void
    {
        parent::setUp();

        $this->getContainer();
        $this->portalId = $this->createPortal($this->go1, ['title' => 'qa.mygo1.com']);
        $this->courseId = $this->createCourse($this->go1, ['instance_id' => $this->portalId]);
    }

    public function test()
    {
        $c = $this->getContainer();
        $embedder = new LoCustomTagCreateEventEmbedder($this->go1, $c['access_checker']);
        $embedded = $embedder->embedded((object)[
            'instance_id' => $this->portalId,
            'lo_id'       => $this->courseId,
            'tag'         => "Foo",
            'status'      => 1,
        ]);
        $this->assertEquals('qa.mygo1.com', $embedded['portal']->title);
        $this->assertEquals($this->courseId, $embedded['lo']->id);
    }
}
