<?php

namespace go1\util\tests\award;

use go1\util\award\AwardEnrolmentHelper;
use go1\util\edge\EdgeTypes;
use go1\util\schema\mock\AwardMockTrait;
use go1\util\schema\mock\UserMockTrait;
use go1\util\tests\UtilTestCase;

class AwardEnrolmentHelperTest extends UtilTestCase
{
    use UserMockTrait;
    use AwardMockTrait;

    public function testGetAssessorIds()
    {
        $awardId = $this->createAward($this->db);
        $enrolmentId = $this->createAwardEnrolment($this->db, [
            'award_id'    => $awardId,
            'user_id'     => 1,
            'instance_id' => 1,
        ]);

        $assessorId1 = $this->createUser($this->db, ['mail' => 'assessor1@gmail.com']);
        $assessorId2 = $this->createUser($this->db, ['mail' => 'assessor2@gmail.com']);
        $assessorId3 = $this->createUser($this->db, ['mail' => 'assessor3@gmail.com']);

        $this->link($this->db, EdgeTypes::HAS_AWARD_TUTOR_ENROLMENT_EDGE, $assessorId1, $enrolmentId);
        $this->link($this->db, EdgeTypes::HAS_AWARD_TUTOR_ENROLMENT_EDGE, $assessorId2, $enrolmentId);
        $this->link($this->db, EdgeTypes::HAS_AWARD_TUTOR_ENROLMENT_EDGE, $assessorId3, $enrolmentId);


        $assessorIds = AwardEnrolmentHelper::assessorIds($this->db, $enrolmentId);

        $this->assertEquals($assessorIds, [$assessorId1, $assessorId2, $assessorId3]);
    }

    public function testLoad()
    {
        $awardId = $this->createAward($this->db);
        $enrolmentId1 = $this->createAwardEnrolment($this->db, [
            'award_id'    => $awardId,
            'user_id'     => 1,
            'instance_id' => 1,
        ]);
        $enrolmentId2 = $this->createAwardEnrolment($this->db, [
            'award_id'    => $awardId,
            'user_id'     => 2,
            'instance_id' => 1,
        ]);

        $enrolments = AwardEnrolmentHelper::loadMultiple($this->db, [$enrolmentId1, $enrolmentId2]);

        $this->assertEquals(2, count($enrolments));
        $this->assertEquals([$enrolmentId1, $enrolmentId2], [$enrolments[0]->id, $enrolments[1]->id]);
    }

    public function testFind()
    {
        $userId = $instanceId = 1;
        $awardId = $this->createAward($this->db);
        $enrolmentId = $this->createAwardEnrolment($this->db, ['award_id' => $awardId, 'user_id' => $userId, 'instance_id' => $instanceId]);

        $this->assertEquals($enrolmentId, AwardEnrolmentHelper::find($this->db, $awardId, $userId, $instanceId)->id);
        $this->assertFalse(AwardEnrolmentHelper::find($this->db, $awardId, $userId, 99));
    }
}
