<?php

namespace go1\util\schema\tests;

use go1\util\assignment\AssignmentHelper;
use go1\util\schema\mock\AssignmentMockTrait;
use go1\util\tests\UtilTestCase;

class AssignmentHelperTest extends UtilTestCase
{
    use AssignmentMockTrait;

    private $fooAssignmentId; // valid data
    private $barAssignmentId; // invalid data
    private $bazAssignmentId; // empty data

    public function setUp()
    {
        parent::setUp();

        $this->fooAssignmentId = $this->createAssignment($this->db, ['data' => json_encode(['foo' => 'bar'])]);
        $this->barAssignmentId = $this->createAssignment($this->db, ['data' => 'invalid data']);
        $this->bazAssignmentId = $this->createAssignment($this->db, ['data' => '']);
    }

    public function testLoad()
    {
        $fooAssignment = AssignmentHelper::load($this->db, $this->fooAssignmentId);
        $barAssignment = AssignmentHelper::load($this->db, $this->barAssignmentId);
        $bazAssignment = AssignmentHelper::load($this->db, $this->bazAssignmentId);

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
}
