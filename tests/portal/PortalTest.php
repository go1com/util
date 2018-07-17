<?php

namespace go1\util\tests;

use Doctrine\DBAL\Connection;
use go1\util\portal\PortalHelper;
use go1\util\schema\mock\PortalMockTrait;
use go1\util\schema\mock\LoMockTrait;

class PortalHelperTest extends UtilTestCase
{
    use PortalMockTrait;
    use LoMockTrait;

    public function testHelper()
    {
        $instanceId = $this->createPortal($this->db, ['title' => 'qa.mygo1.com']);
        $courseId = $this->createCourse($this->db, ['instance_id' => $instanceId]);

        // Test ::load()
        $this->assertEquals($instanceId, PortalHelper::load($this->db, $instanceId)->id, 'Can load portal by ID.');
        $this->assertEquals($instanceId, PortalHelper::load($this->db, 'qa.mygo1.com')->id, 'Can load portal by Title');

        // Test ::titleFromLoId()
        $this->assertEquals('qa.mygo1.com', PortalHelper::titleFromLoId($this->db, $courseId));
    }

    public function testUpdate()
    {
        $instanceId = $this->createPortal($this->db, ['title' => 'qa.mygo1.com', 'version' => 'v2.11.0']);
        PortalHelper::updateVersion($this->db, $this->queue, PortalHelper::STABLE_VERSION, $instanceId);
        $version = PortalHelper::load($this->db, $instanceId)->version;
        $this->assertEquals(PortalHelper::STABLE_VERSION, $version);
    }

    public function testLoadFromLoId()
    {
        $instanceId = $this->createPortal($this->db, ['title' => 'qa.mygo1.com', 'version' => 'v2.11.0']);
        $courseId = $this->createCourse($this->db, ['instance_id' => $instanceId]);

        $mockDb = $this->getMockBuilder(Connection::class)
            ->disableOriginalConstructor()
            ->setMethods(['executeQuery'])
            ->getMock();
        $mockDb->expects($this->once())
            ->method('executeQuery')
            ->willReturn($this->db->executeQuery('SELECT gc_instance.* FROM gc_instance'
                . ' INNER JOIN gc_lo ON gc_instance.id = gc_lo.instance_id'
                . ' WHERE gc_lo.id = ?',
                [$courseId]));

        $portal = PortalHelper::loadFromLoId($mockDb, $courseId);
        $portal = PortalHelper::loadFromLoId($mockDb, $courseId);
    }
}
