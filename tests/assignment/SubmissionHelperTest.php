<?php

namespace go1\util\tests\assignment;

use go1\util\assignment\SubmissionHelper;
use go1\util\schema\mock\AssignmentMockTrait;
use go1\util\tests\UtilTestCase;

class SubmissionHelperTest extends UtilTestCase
{
    use AssignmentMockTrait;

    public function testLoad()
    {
        $id = $this->createSubmission($this->db);

        $this->assertTrue(is_object(SubmissionHelper::load($this->db, $id)));
        $this->assertFalse(SubmissionHelper::load($this->db, 123));
    }

    public function testLoadByEnrolmentId()
    {
        $this->createSubmission($this->db, ['enrolment_id' => 123]);
        $this->createSubmission($this->db, ['enrolment_id' => 234]);

        $this->assertTrue(is_object(SubmissionHelper::loadByEnrolmentId($this->db, 123)));
        $this->assertTrue(is_object(SubmissionHelper::loadByEnrolmentId($this->db, 234)));
        $this->assertFalse(SubmissionHelper::loadByEnrolmentId($this->db, 345));
    }

    public function testGetSubmittedDate()
    {
        $id = $this->createSubmission($this->db);
        $this->createSubmissionRevision($this->db, ['submission_id' => $id, 'created' => 345]);
        $this->createSubmissionRevision($this->db, ['submission_id' => $id, 'created' => 123]);
        $this->createSubmissionRevision($this->db, ['submission_id' => $id, 'created' => 234]);

        $this->assertEquals(234, SubmissionHelper::getSubmittedDate($this->db, $id));
    }

    public function testGetMarkedDate()
    {
        $id = $this->createSubmission($this->db);
        $this->createSubmissionRevision($this->db, ['submission_id' => $id, 'actor_id' => 0, 'updated' => 345]);
        $this->createSubmissionRevision($this->db, ['submission_id' => $id, 'actor_id' => 0, 'updated' => 234]);
        $this->createSubmissionRevision($this->db, ['submission_id' => $id, 'actor_id' => 222, 'updated' => 121]);
        $this->createSubmissionRevision($this->db, ['submission_id' => $id, 'actor_id' => 223, 'updated' => 56]);
        $this->createSubmissionRevision($this->db, ['submission_id' => $id, 'actor_id' => 0, 'updated' => 87]);

        $this->assertEquals(56, SubmissionHelper::getMarkedDate($this->db, $id));
    }
}
