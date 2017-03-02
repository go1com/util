<?php

namespace go1\util\tests;

use go1\util\EdgeTypes;
use go1\util\EnrolmentHelper;
use go1\util\schema\mock\EnrolmentMockTrait;
use go1\util\schema\mock\UserMockTrait;

class EnrolmentHelperTest extends UtilTestCase
{
    use UserMockTrait;
    use EnrolmentMockTrait;

    public function testAssessor()
    {
        $enrolmentId = $this->createEnrolment($this->db, ['lo_id' => 1, 'profile_id' => 1]);
        $assessor1Id = $this->createUser($this->db, ['mail' => 'assessor1@mail.com']);
        $assessor2Id = $this->createUser($this->db, ['mail' => 'assessor2@mail.com']);
        $this->createUser($this->db, ['mail' => 'assessor3@mail.com']);

        $this->link($this->db, EdgeTypes::HAS_TUTOR_ENROLMENT_EDGE, $assessor1Id, $enrolmentId);
        $this->link($this->db, EdgeTypes::HAS_TUTOR_ENROLMENT_EDGE, $assessor2Id, $enrolmentId);

        $assessors = EnrolmentHelper::assessors($this->db, $enrolmentId);
        $this->assertEquals(2, count($assessors));
        $this->assertEquals($assessor1Id, $assessors[0]);
        $this->assertEquals($assessor2Id, $assessors[1]);
    }
}
