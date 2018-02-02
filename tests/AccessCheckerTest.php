<?php

namespace go1\util\tests;

use go1\util\AccessChecker;
use go1\util\award\AwardItemTypes;
use go1\util\edge\EdgeTypes;
use go1\util\schema\mock\AwardMockTrait;
use go1\util\schema\mock\UserMockTrait;
use Symfony\Component\HttpFoundation\Request;

class AccessCheckerTest extends UtilTestCase
{
    use UserMockTrait;
    use AwardMockTrait;

    public function testVirtualAccount()
    {
        $userId = $this->createUser($this->db, ['instance' => 'accounts.gocatalyze.com']);
        $instance = 'portal.mygo1.com';
        $accountId = $this->createUser($this->db, ['instance' => $instance]);
        $this->link($this->db, EdgeTypes::HAS_ACCOUNT_VIRTUAL, $userId, $accountId);

        $payload = $this->getPayload([]);
        $req = new Request;
        $req->attributes->set('jwt.payload', $payload);

        $access = new AccessChecker();
        $account1 = $access->validUser($req, $instance);
        $this->assertFalse($account1);

        $account2 = $access->validUser($req, $instance, $this->db);
        $this->assertEquals($accountId, $account2->id);
    }

    public function testIsStudentManager()
    {
        $manager2Id = $this->createUser($this->db, ['mail' => $manager2Mail = 'manager2@mail.com', 'instance' => $accountsName = 'accounts.gocatalyze.com']);
        $managerId = $this->createUser($this->db, ['mail' => $managerMail = 'manager@mail.com', 'instance' => $accountsName = 'accounts.gocatalyze.com']);
        $studentId = $this->createUser($this->db, ['mail' => $studentMail = 'student@mail.com', 'instance' => $instanceName = 'portal.mygo1.com']);
        $this->link($this->db, EdgeTypes::HAS_MANAGER, $studentId, $managerId);

        # Is manager
        $req = new Request;
        $req->attributes->set('jwt.payload', $this->getPayload(['id' => $managerId, 'mail' => $managerMail]));
        $this->assertTrue((new AccessChecker)->isStudentManager($this->db, $req, $studentMail, $instanceName, EdgeTypes::HAS_MANAGER));

        # Is not manager
        $req = new Request;
        $req->attributes->set('jwt.payload', $this->getPayload(['id' => $manager2Id, 'mail' => $manager2Mail]));
        $this->assertFalse((new AccessChecker)->isStudentManager($this->db, $req, $studentMail, $instanceName, EdgeTypes::HAS_MANAGER));
    }

    public function testIsAwardAssessor()
    {
        $portalName = 'foo.bar';
        $awardLevel1OldRevId = 1;
        $awardLevel1Id = $this->createAward($this->db, ['instance' => $portalName, 'revision_id' => $awardLevel1RevId = 2]);
        $awardLevel2Id = $this->createAward($this->db, ['instance' => $portalName, 'revision_id' => $awardLevel2RevId = 3]);
        $awardLevel3Id = $this->createAward($this->db, ['instance' => $portalName, 'revision_id' => $awardLevel3RevId = 4]);
        $otherAwardLevel1Id = $this->createAward($this->db, ['instance' => $portalName, 'revision_id' => $otherAwardLevel1RevId = 5]);

        $this->createAwardItem($this->db, $awardLevel1OldRevId, AwardItemTypes::AWARD, $awardLevel2Id);
        $this->createAwardItem($this->db, $awardLevel1RevId, AwardItemTypes::AWARD, $awardLevel2Id);
        $this->createAwardItem($this->db, $awardLevel2RevId, AwardItemTypes::AWARD, $awardLevel3Id);
        $this->createAwardItem($this->db, $otherAwardLevel1RevId, AwardItemTypes::AWARD, $awardLevel2Id);

        $assessorLevel1Id = $this->createUser($this->db, ['mail' => 'assessorl1@mail.com', 'instance' => $portalName]);
        $assessorLevel2Id = $this->createUser($this->db, ['mail' => 'assessorl2@mail.com', 'instance' => $portalName]);
        $assessorLevel3Id = $this->createUser($this->db, ['mail' => 'assessorl3@mail.com', 'instance' => $portalName]);
        $otherAssessorLevel1Id = $this->createUser($this->db, ['mail' => 'other.assessorl1@mail.com', 'instance' => $portalName]);

        $this->link($this->db, EdgeTypes::AWARD_ASSESSOR, $awardLevel1Id, $assessorLevel1Id);
        $this->link($this->db, EdgeTypes::AWARD_ASSESSOR, $awardLevel2Id, $assessorLevel2Id);
        $this->link($this->db, EdgeTypes::AWARD_ASSESSOR, $awardLevel3Id, $assessorLevel3Id);
        $this->link($this->db, EdgeTypes::AWARD_ASSESSOR, $otherAwardLevel1Id, $otherAssessorLevel1Id);

        $checker = new AccessChecker();
        $this->assertTrue($checker->isAwardAssessor($this->db, $this->db, $awardLevel1Id, $assessorLevel1Id));
        $this->assertTrue($checker->isAwardAssessor($this->db, $this->db, $awardLevel2Id, $assessorLevel1Id));
        $this->assertTrue($checker->isAwardAssessor($this->db, $this->db, $awardLevel3Id, $assessorLevel1Id));
        $this->assertTrue($checker->isAwardAssessor($this->db, $this->db, $awardLevel2Id, $assessorLevel2Id));
        $this->assertTrue($checker->isAwardAssessor($this->db, $this->db, $awardLevel3Id, $assessorLevel2Id));
        $this->assertTrue($checker->isAwardAssessor($this->db, $this->db, $awardLevel3Id, $assessorLevel3Id));
        $this->assertTrue($checker->isAwardAssessor($this->db, $this->db, $otherAwardLevel1Id, $otherAssessorLevel1Id));
        $this->assertTrue($checker->isAwardAssessor($this->db, $this->db, $awardLevel2Id, $otherAssessorLevel1Id));
        $this->assertTrue($checker->isAwardAssessor($this->db, $this->db, $awardLevel3Id, $otherAssessorLevel1Id));
        $this->assertFalse($checker->isAwardAssessor($this->db, $this->db, $awardLevel2Id, $assessorLevel1Id, false));
        $this->assertFalse($checker->isAwardAssessor($this->db, $this->db, $awardLevel3Id, $assessorLevel1Id, false));
        $this->assertFalse($checker->isAwardAssessor($this->db, $this->db, $awardLevel3Id, $assessorLevel2Id, false));
        $this->assertFalse($checker->isAwardAssessor($this->db, $this->db, $awardLevel1Id, $otherAssessorLevel1Id));
        $this->assertFalse($checker->isAwardAssessor($this->db, $this->db, $awardLevel2Id, $otherAssessorLevel1Id, false));
        $this->assertFalse($checker->isAwardAssessor($this->db, $this->db, $awardLevel3Id, $otherAssessorLevel1Id, false));
    }
}
