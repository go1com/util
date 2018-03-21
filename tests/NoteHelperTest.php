<?php

namespace go1\util\tests;

use Doctrine\DBAL\Connection;
use go1\util\note\NoteHelper;
use go1\util\portal\PortalChecker;
use go1\util\schema\mock\GroupMockTrait;
use go1\util\schema\mock\PortalMockTrait;
use go1\util\schema\mock\LoMockTrait;
use go1\util\schema\mock\NoteMockTrait;

class NoteHelperTest extends UtilTestCase
{
    use PortalMockTrait;
    use LoMockTrait;
    use GroupMockTrait;
    use NoteMockTrait;

    public function loadPortalData()
    {
        parent::setUp();

        $instanceId = $this->createPortal($this->db, []);
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

    public function testLoadNoteComment()
    {
        $userId = $this->createUser($this->db, ['data' => ['avatar' => ['uri' => 'https://avatar.com/a.jpg']]]);
        $id = $this->createNoteComment($this->db, [
            'note_id' => 1000,
            'user_id' => $userId,
            'description' => 'note comment description'
        ]);

        $comment = NoteHelper::loadComment($this->db, $id, $this->db);
        $this->assertEquals(1000, $comment->note_id);
        $this->assertEquals('note comment description', $comment->description);
        $this->assertEquals($userId, $comment->user->id);
        $this->assertEquals("A T", $comment->user->name);
        $this->assertEquals('https://avatar.com/a.jpg', $comment->user->avatar);
    }
}
