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
        $id = $this->createSubmission($this->go1);

        $this->assertTrue(is_object(SubmissionHelper::load($this->go1, $id)));
        $this->assertFalse(SubmissionHelper::load($this->go1, 123));
    }

    public function testLoadByEnrolmentId()
    {
        $this->createSubmission($this->go1, ['enrolment_id' => 123]);
        $this->createSubmission($this->go1, ['enrolment_id' => 234]);

        $this->assertTrue(is_object(SubmissionHelper::loadByEnrolmentId($this->go1, 123)));
        $this->assertTrue(is_object(SubmissionHelper::loadByEnrolmentId($this->go1, 234)));
        $this->assertFalse(SubmissionHelper::loadByEnrolmentId($this->go1, 345));
    }

    public function testGetSubmittedDate()
    {
        $id = $this->createSubmission($this->go1);
        $this->createSubmissionRevision($this->go1, ['submission_id' => $id, 'created' => 345]);
        $this->createSubmissionRevision($this->go1, ['submission_id' => $id, 'created' => 123]);
        $this->createSubmissionRevision($this->go1, ['submission_id' => $id, 'created' => 234]);

        $this->assertEquals(234, SubmissionHelper::getSubmittedDate($this->go1, $id));
    }

    public function testGetMarkedDate()
    {
        $id = $this->createSubmission($this->go1);
        $this->createSubmissionRevision($this->go1, ['submission_id' => $id, 'actor_id' => 0, 'updated' => 345]);
        $this->createSubmissionRevision($this->go1, ['submission_id' => $id, 'actor_id' => 0, 'updated' => 234]);
        $this->createSubmissionRevision($this->go1, ['submission_id' => $id, 'actor_id' => 222, 'updated' => 121]);
        $this->createSubmissionRevision($this->go1, ['submission_id' => $id, 'actor_id' => 223, 'updated' => 56]);
        $this->createSubmissionRevision($this->go1, ['submission_id' => $id, 'actor_id' => 0, 'updated' => 87]);

        $this->assertEquals(56, SubmissionHelper::getMarkedDate($this->go1, $id));
    }
}
