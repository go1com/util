<?php

namespace go1\util\tests;

use Doctrine\DBAL\Connection;
use go1\util\note\NoteHelper;
use go1\util\portal\PortalChecker;
use go1\util\schema\mock\GroupMockTrait;
use go1\util\schema\mock\InstanceMockTrait;
use go1\util\schema\mock\LoMockTrait;

class NoteHelperTest extends UtilTestCase
{
    use InstanceMockTrait;
    use LoMockTrait;
    use GroupMockTrait;

    public function loadPortalData()
    {
        parent::setUp();

        $instanceId = $this->createInstance($this->db, []);
        $loId = $this->createLO($this->db, ['instance_id' => $instanceId]);
        $groupId = $this->createGroup($this->db, ['instance_id' => $instanceId]);

        return [
            [$this->db, $instanceId, 'portal', $instanceId],
            [$this->db, $instanceId, 'lo', $loId],
            [$this->db, $instanceId, 'group', $groupId],
        ];
    }

    /**
     * @dataProvider loadPortalData
     */
    public function testLoadPortal(Connection $db, $instanceId, $entityType, $entityId)
    {
        $portal = (new NoteHelper())
            ->setConnection($db, $db)
            ->loadPortal($entityType, $entityId, new PortalChecker);

        $this->assertEquals($instanceId, $portal->id);
    }
}
