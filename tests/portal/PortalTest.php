<?php

namespace go1\util\tests;

use Doctrine\DBAL\Connection;
use go1\util\portal\PortalHelper;
use go1\util\schema\mock\LoMockTrait;
use go1\util\schema\mock\PortalMockTrait;

class PortalHelperTest extends UtilCoreTestCase
{
    use PortalMockTrait;
    use LoMockTrait;

    public function testHelper()
    {
        $portalId = $this->createPortal($this->go1, ['title' => 'qa.mygo1.com']);
        $courseId = $this->createCourse($this->go1, ['instance_id' => $portalId]);

        // Test ::load()
        $this->assertEquals($portalId, PortalHelper::load($this->go1, $portalId)->id, 'Can load portal by ID.');
        $this->assertEquals($portalId, PortalHelper::load($this->go1, 'qa.mygo1.com')->id, 'Can load portal by Title');

        // Test ::titleFromLoId()
        $this->assertEquals('qa.mygo1.com', PortalHelper::titleFromLoId($this->go1, $courseId));
    }

    public function testUpdate()
    {
        $instanceId = $this->createPortal($this->go1, ['title' => 'qa.mygo1.com', 'version' => 'v2.11.0']);
        PortalHelper::updateVersion($this->go1, $this->queue, PortalHelper::STABLE_VERSION, $instanceId);
        $version = PortalHelper::load($this->go1, $instanceId)->version;
        $this->assertEquals(PortalHelper::STABLE_VERSION, $version);
    }

    public function testLoadFromLoId()
    {
        $instanceId = $this->createPortal($this->go1, ['title' => 'qa.mygo1.com', 'version' => 'v2.11.0']);
        $courseId = $this->createCourse($this->go1, ['instance_id' => $instanceId]);

        $mockDb = $this->getMockBuilder(Connection::class)
                       ->disableOriginalConstructor()
                       ->setMethods(['executeQuery'])
                       ->getMock();
        $mockDb->expects($this->once())
               ->method('executeQuery')
               ->willReturn($this->go1->executeQuery('SELECT gc_instance.* FROM gc_instance'
                   . ' INNER JOIN gc_lo ON gc_instance.id = gc_lo.instance_id'
                   . ' WHERE gc_lo.id = ?',
                   [$courseId]));

        $portal = PortalHelper::loadFromLoId($mockDb, $courseId);
        $portal = PortalHelper::loadFromLoId($mockDb, $courseId);
    }
}
