<?php

namespace go1\util\tests;

use go1\util\portal\PortalHelper;
use go1\util\schema\mock\InstanceMockTrait;
use go1\util\schema\mock\LoMockTrait;

class PortalHelperTest extends UtilTestCase
{
    use InstanceMockTrait;
    use LoMockTrait;

    public function testHelper()
    {
        $instanceId = $this->createInstance($this->db, ['title' => 'qa.mygo1.com']);
        $courseId = $this->createCourse($this->db, ['instance_id' => $instanceId]);

        $this->assertEquals('qa.mygo1.com', PortalHelper::titleFromLoId($this->db, $courseId));
    }
}
