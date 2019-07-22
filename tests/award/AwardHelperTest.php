<?php

namespace go1\util\tests\award;


use go1\util\award\AwardHelper;
use go1\util\award\AwardItemTypes;
use go1\util\edge\EdgeTypes;
use go1\util\schema\mock\AwardMockTrait;
use go1\util\schema\mock\UserMockTrait;
use go1\util\tests\UtilCoreTestCase;

class AwardHelperTest extends UtilCoreTestCase
{
    use UserMockTrait;
    use AwardMockTrait;

    public function testGetAssessorIds()
    {
        $awardId = $this->createAward($this->go1);
        $assessorId1 = $this->createUser($this->go1, ['mail' => 'assessor1@gmail.com']);
        $assessorId2 = $this->createUser($this->go1, ['mail' => 'assessor2@gmail.com']);
        $assessorId3 = $this->createUser($this->go1, ['mail' => 'assessor3@gmail.com']);

        $this->link($this->go1, EdgeTypes::AWARD_ASSESSOR, $awardId, $assessorId1);
        $this->link($this->go1, EdgeTypes::AWARD_ASSESSOR, $awardId, $assessorId2);
        $this->link($this->go1, EdgeTypes::AWARD_ASSESSOR, $awardId, $assessorId3);

        $assessorIds = AwardHelper::assessorIds($this->go1, $awardId);
        $this->assertEquals([$assessorId1, $assessorId2, $assessorId3], $assessorIds);
    }

    public function testGetAwardParentId()
    {
        $awardId = $this->createAward($this->go1);
        $childAwardId = $this->createAward($this->go1);
        $award = AwardHelper::load($this->go1, $awardId);

        $this->createAwardItem($this->go1, $award->revision_id, AwardItemTypes::AWARD, $childAwardId);

        $this->assertEquals([$awardId], AwardHelper::awardParentIds($this->go1, [$childAwardId]));
    }
}
