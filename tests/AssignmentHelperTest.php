<?php

namespace go1\util\schema\tests;

use go1\util\assignment\AssignmentHelper;
use go1\util\edge\EdgeTypes;
use go1\util\lo\LiTypes;
use go1\util\schema\mock\AssignmentMockTrait;
use go1\util\schema\mock\EnrolmentMockTrait;
use go1\util\schema\mock\LoMockTrait;
use go1\util\tests\UtilTestCase;

class AssignmentHelperTest extends UtilTestCase
{
    use AssignmentMockTrait;
    use EnrolmentMockTrait;
    use LoMockTrait;

    private $fooAssignmentId; // valid data
    private $barAssignmentId; // invalid data
    private $bazAssignmentId; // empty data

    public function setUp()
    {
        parent::setUp();

        $this->fooAssignmentId = $this->createAssignment($this->go1, ['data' => json_encode(['foo' => 'bar'])]);
        $this->barAssignmentId = $this->createAssignment($this->go1, ['data' => 'invalid data']);
        $this->bazAssignmentId = $this->createAssignment($this->go1, ['data' => '']);
    }

    public function testLoad()
    {
        $fooAssignment = AssignmentHelper::load($this->go1, $this->fooAssignmentId);
        $barAssignment = AssignmentHelper::load($this->go1, $this->barAssignmentId);
        $bazAssignment = AssignmentHelper::load($this->go1, $this->bazAssignmentId);

        $this->assertTrue(is_object($fooAssignment));
        $this->assertEquals($this->fooAssignmentId, $fooAssignment->id);
        $this->assertEquals((object)['foo' => 'bar'], $fooAssignment->data);
        $this->assertTrue(is_object($barAssignment));
        $this->assertEquals($this->barAssignmentId, $barAssignment->id);
        $this->assertFalse(isset($barAssignment->data));
        $this->assertTrue(is_object($bazAssignment));
        $this->assertEquals($this->bazAssignmentId, $bazAssignment->id);
        $this->assertFalse(isset($bazAssignment->data));
    }

    public function testLocateLiAssignment()
    {
        $fooAssignmentId = $this->createAssignment($this->go1, ['data' => json_encode(['foo' => 'bar'])]);
        $liAssignmentId = $this->createLO($this->go1, ['type' => LiTypes::ASSIGNMENT, 'title' => 'Example Assignment Li', 'remote_id' => $fooAssignmentId]);

        $li = AssignmentHelper::locateLiAssignment($this->go1, $fooAssignmentId);
        $this->assertTrue(is_object($li));
        $this->assertEquals($liAssignmentId, $li->id);
    }

    public function testGetEnrolment() {
        $fooAssignmentId = $this->createAssignment($this->go1, ['data' => json_encode(['foo' => 'bar'])]);
        $liAssignmentId = $this->createLO($this->go1, ['type' => LiTypes::ASSIGNMENT, 'title' => 'Example Assignment Li', 'remote_id' => $fooAssignmentId]);
        $moduleId = $this->createModule($this->go1);
        $this->link($this->go1, EdgeTypes::HAS_LI, $moduleId, $liAssignmentId);
        $liEnrolmentId = $this->createEnrolment($this->go1, ['lo_id' => $liAssignmentId, 'profile_id' => $profileId = 123, 'parent_lo_id' => $moduleId]);

        $enrolment = AssignmentHelper::getEnrolment($this->go1, $profileId, $fooAssignmentId);
        $this->assertTrue(is_object($enrolment));
        $this->assertEquals($liEnrolmentId, $enrolment->id);
        $this->assertEquals($moduleId, $enrolment->parent_lo_id);
    }
}
