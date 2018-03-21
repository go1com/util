<?php

namespace go1\util\tests\lo;

use go1\util\edge\EdgeTypes;
use go1\util\lo\LoChecker;
use go1\util\lo\LoHelper;
use go1\util\schema\mock\PortalMockTrait;
use go1\util\schema\mock\LoMockTrait;
use go1\util\schema\mock\UserMockTrait;
use go1\util\tests\UtilTestCase;

class LoCheckerTest extends UtilTestCase
{
    use PortalMockTrait;
    use LoMockTrait;
    use UserMockTrait;

    public function testIsCourseAuthorTest()
    {
        $instanceId = $this->createPortal($this->db, ['title' => 'qa.mygo1.com']);
        $userId = $this->createUser($this->db, ['instance' => 'qa.mygo1.com']);
        $courseId = $this->createCourse($this->db, ['instance_id' => $instanceId]);
        $moduleId = $this->createModule($this->db, ['instance_id' => $instanceId]);
        $this->link($this->db, EdgeTypes::HAS_MODULE, $courseId, $moduleId);
        $this->link($this->db, EdgeTypes::HAS_AUTHOR_EDGE, $courseId, $userId);

        $checker = new LoChecker;
        $this->assertEquals(true, $checker->isAuthor($this->db, $courseId, $userId));
        $this->assertEquals(true, $checker->isModuleAuthor($this->db, $moduleId, $userId));
        $this->assertEquals(false, $checker->isAuthor($this->db, $courseId + 444, $userId));
        $this->assertEquals(false, $checker->isModuleAuthor($this->db, $moduleId + 555, $userId));
    }

    public function testAuthorIds()
    {
        // Setup data
        $userId = $this->createUser($this->db, ['instance' => 'accounts.gocatalyze.com', 'mail' => 'user@qa.mygo1.com']);
        $userId2 = $this->createUser($this->db, ['instance' => 'accounts.gocatalyze.com', 'mail' => 'user2@qa.mygo1.com']);
        $loId = $this->createLO($this->db);

        $this->link($this->db, EdgeTypes::HAS_AUTHOR_EDGE, $loId, $userId);
        $this->link($this->db, EdgeTypes::HAS_AUTHOR_EDGE, $loId, $userId2);

        // Check
        $this->assertEquals([$userId, $userId2], LoChecker::authorIds($this->db, $loId));
    }

    public function testAllowDiscussion()
    {
        $loId1 = $this->createLO($this->db);
        $lo1 = LoHelper::load($this->db, $loId1);
        $this->assertTrue(LoChecker::allowDiscussion($lo1));

        $loId2 = $this->createLO($this->db, ['data' => [LoHelper::DISCUSSION_ALLOW => false]]);
        $lo2 = LoHelper::load($this->db, $loId2);
        $this->assertFalse(LoChecker::allowDiscussion($lo2));
    }

    public function testPassRate()
    {
        $id1 = $this->createLO($this->db);
        $lo1 = LoHelper::load($this->db, $id1);
        $this->assertEquals(0, LoChecker::passRate($lo1));

        $id2 = $this->createLO($this->db, ['data' => [LoHelper::PASS_RATE => 80]]);
        $lo2 = LoHelper::load($this->db, $id2);
        $this->assertEquals(80, LoChecker::passRate($lo2));
    }
}
