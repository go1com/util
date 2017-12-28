<?php

namespace go1\util\tests;

use go1\util\assignment\SubmissionHelper;
use go1\util\schema\mock\AssignmentMockTrait;

class SubmissionHelperTest extends UtilTestCase
{
    use AssignmentMockTrait;

    public function testLoad()
    {
        $id = $this->createSubmission($this->db);

        $this->assertTrue(is_object(SubmissionHelper::load($this->db, $id)));
        $this->assertTrue(is_null(SubmissionHelper::load($this->db, 123)));
    }

    public function testLoadByEnrolmentId()
    {
        $id = $this->createSubmission($this->db, ['enrolment_id' => 123]);
        $id = $this->createSubmission($this->db, ['enrolment_id' => 234]);

        $this->assertTrue(is_object(SubmissionHelper::loadByEnrolmentId($this->db, 123)));
        $this->assertTrue(is_object(SubmissionHelper::loadByEnrolmentId($this->db, 234)));
        $this->assertTrue(is_null(SubmissionHelper::loadByEnrolmentId($this->db, 234)));
    }
}
