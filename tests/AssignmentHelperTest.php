<?php

namespace go1\util\schema\tests;

use go1\util\assignment\AssignmentHelper;
use go1\util\schema\mock\AssignmentMockTrait;
use go1\util\tests\UtilTestCase;

class AssignmentHelperTest extends UtilTestCase
{
    use AssignmentMockTrait;

    private $assignmentId;

    public function setUp()
    {
        parent::setUp();

        $this->assignmentId = $this->createAssignment($this->db);
    }

    public function testLoad()
    {
        $assignment = AssignmentHelper::load($this->db, $this->assignmentId);

        $this->assertTrue(is_object($assignment));
        $this->assertEquals($this->assignmentId, $assignment->id);
    }
}
