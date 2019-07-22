<?php

namespace go1\util\tests\award;

use go1\util\AccessChecker;
use go1\util\award\AwardItemTypes;
use go1\util\edge\EdgeTypes;
use go1\util\schema\mock\AwardMockTrait;
use go1\util\schema\mock\UserMockTrait;
use go1\util\tests\UtilCoreTestCase;

class AccessCheckerTest extends UtilCoreTestCase
{
    use UserMockTrait;
    use AwardMockTrait;

    public function testIsAwardAssessor()
    {
        $portalName = 'foo.bar';
        $awardLevel1OldRevId = 1;
        $awardLevel1Id = $this->createAward($this->go1, ['instance' => $portalName, 'revision_id' => $awardLevel1RevId = 2]);
        $awardLevel2Id = $this->createAward($this->go1, ['instance' => $portalName, 'revision_id' => $awardLevel2RevId = 3]);
        $awardLevel3Id = $this->createAward($this->go1, ['instance' => $portalName, 'revision_id' => $awardLevel3RevId = 4]);
        $otherAwardLevel1Id = $this->createAward($this->go1, ['instance' => $portalName, 'revision_id' => $otherAwardLevel1RevId = 5]);

        $this->createAwardItem($this->go1, $awardLevel1OldRevId, AwardItemTypes::AWARD, $awardLevel2Id);
        $this->createAwardItem($this->go1, $awardLevel1RevId, AwardItemTypes::AWARD, $awardLevel2Id);
        $this->createAwardItem($this->go1, $awardLevel2RevId, AwardItemTypes::AWARD, $awardLevel3Id);
        $this->createAwardItem($this->go1, $otherAwardLevel1RevId, AwardItemTypes::AWARD, $awardLevel2Id);

        $assessorLevel1Id = $this->createUser($this->go1, ['mail' => 'assessorl1@mail.com', 'instance' => $portalName]);
        $assessorLevel2Id = $this->createUser($this->go1, ['mail' => 'assessorl2@mail.com', 'instance' => $portalName]);
        $assessorLevel3Id = $this->createUser($this->go1, ['mail' => 'assessorl3@mail.com', 'instance' => $portalName]);
        $otherAssessorLevel1Id = $this->createUser($this->go1, ['mail' => 'other.assessorl1@mail.com', 'instance' => $portalName]);

        $this->link($this->go1, EdgeTypes::AWARD_ASSESSOR, $awardLevel1Id, $assessorLevel1Id);
        $this->link($this->go1, EdgeTypes::AWARD_ASSESSOR, $awardLevel2Id, $assessorLevel2Id);
        $this->link($this->go1, EdgeTypes::AWARD_ASSESSOR, $awardLevel3Id, $assessorLevel3Id);
        $this->link($this->go1, EdgeTypes::AWARD_ASSESSOR, $otherAwardLevel1Id, $otherAssessorLevel1Id);

        $checker = new AccessChecker();
        $this->assertTrue($checker->isAwardAssessor($this->go1, $this->go1, $awardLevel1Id, $assessorLevel1Id));
        $this->assertTrue($checker->isAwardAssessor($this->go1, $this->go1, $awardLevel2Id, $assessorLevel1Id));
        $this->assertTrue($checker->isAwardAssessor($this->go1, $this->go1, $awardLevel3Id, $assessorLevel1Id));
        $this->assertTrue($checker->isAwardAssessor($this->go1, $this->go1, $awardLevel2Id, $assessorLevel2Id));
        $this->assertTrue($checker->isAwardAssessor($this->go1, $this->go1, $awardLevel3Id, $assessorLevel2Id));
        $this->assertTrue($checker->isAwardAssessor($this->go1, $this->go1, $awardLevel3Id, $assessorLevel3Id));
        $this->assertTrue($checker->isAwardAssessor($this->go1, $this->go1, $otherAwardLevel1Id, $otherAssessorLevel1Id));
        $this->assertTrue($checker->isAwardAssessor($this->go1, $this->go1, $awardLevel2Id, $otherAssessorLevel1Id));
        $this->assertTrue($checker->isAwardAssessor($this->go1, $this->go1, $awardLevel3Id, $otherAssessorLevel1Id));
        $this->assertFalse($checker->isAwardAssessor($this->go1, $this->go1, $awardLevel2Id, $assessorLevel1Id, false));
        $this->assertFalse($checker->isAwardAssessor($this->go1, $this->go1, $awardLevel3Id, $assessorLevel1Id, false));
        $this->assertFalse($checker->isAwardAssessor($this->go1, $this->go1, $awardLevel3Id, $assessorLevel2Id, false));
        $this->assertFalse($checker->isAwardAssessor($this->go1, $this->go1, $awardLevel1Id, $otherAssessorLevel1Id));
        $this->assertFalse($checker->isAwardAssessor($this->go1, $this->go1, $awardLevel2Id, $otherAssessorLevel1Id, false));
        $this->assertFalse($checker->isAwardAssessor($this->go1, $this->go1, $awardLevel3Id, $otherAssessorLevel1Id, false));
    }
}
