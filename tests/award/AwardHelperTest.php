<?php

namespace go1\util\tests\award;


use go1\util\award\AwardHelper;
use go1\util\award\AwardItemTypes;
use go1\util\edge\EdgeTypes;
use go1\util\schema\mock\AwardMockTrait;
use go1\util\schema\mock\UserMockTrait;
use go1\util\tests\UtilTestCase;

class AwardHelperTest extends UtilTestCase
{
    use UserMockTrait;
    use AwardMockTrait;

    public function testGetAssessorIds()
    {
        $awardId = $this->createAward($this->db);
        $assessorId1 = $this->createUser($this->db, ['mail' => 'assessor1@gmail.com']);
        $assessorId2 = $this->createUser($this->db, ['mail' => 'assessor2@gmail.com']);
        $assessorId3 = $this->createUser($this->db, ['mail' => 'assessor3@gmail.com']);

        $this->link($this->db, EdgeTypes::AWARD_ASSESSOR, $awardId, $assessorId1);
        $this->link($this->db, EdgeTypes::AWARD_ASSESSOR, $awardId, $assessorId2);
        $this->link($this->db, EdgeTypes::AWARD_ASSESSOR, $awardId, $assessorId3);

        $assessorIds = AwardHelper::assessorIds($this->db, $awardId);
        $this->assertEquals([$assessorId1, $assessorId2, $assessorId3], $assessorIds);
    }

    public function testGetAwardParentId()
    {
        $awardId = $this->createAward($this->db);
        $childAwardId = $this->createAward($this->db);
        $award = AwardHelper::load($this->db, $awardId);

        $this->createAwardItem($this->db, $award->revision_id, AwardItemTypes::AWARD, $childAwardId);

        $this->assertEquals([$awardId], AwardHelper::awardParentIds($this->db, [$childAwardId]));
    }
}
