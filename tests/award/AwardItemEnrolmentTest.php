<?php

namespace go1\util\tests\award;


use go1\util\award\AwardHelper;
use go1\util\award\AwardItemEnrolmentHelper;
use go1\util\award\AwardItemTypes;
use go1\util\schema\mock\AwardMockTrait;
use go1\util\schema\mock\PortalMockTrait;
use go1\util\schema\mock\UserMockTrait;
use go1\util\tests\UtilTestCase;

class AwardItemEnrolmentTest extends UtilTestCase
{
    use PortalMockTrait;
    use UserMockTrait;
    use AwardMockTrait;

    public function testAwardItemEnrolmentParent()
    {
        $loId = 1001;
        $userId = 1011;

        $portalId =  $this->createPortal($this->db, ['title' => 'util.mygo1.com']);
        $awardId = $this->createAward($this->db, ['instance_id' => $portalId]);
        $childAwardId = $this->createAward($this->db, ['instance_id' => $portalId]);
        $award = AwardHelper::load($this->db, $awardId);
        $childAward = AwardHelper::load($this->db, $childAwardId);

        $this->createAwardItem($this->db, $childAward->revision_id, AwardItemTypes::LO, $loId);
        $childItemEnrolId = $this->createAwardItemEnrolment($this->db, [
            'award_id'    => $childAwardId,
            'entity_id'   => $loId,
            'user_id'     => $userId,
            'instance_id' => $portalId,
            'type'        => AwardItemTypes::AWARD,
        ]);

        $this->createAwardItem($this->db, $award->revision_id, AwardItemTypes::AWARD, $childAwardId);
        $parentItemEnrolmentId = $this->createAwardItemEnrolment($this->db, [
            'award_id'    => $awardId,
            'entity_id'   => $childAwardId,
            'user_id'     => $userId,
            'instance_id' => $portalId,
            'type'        => AwardItemTypes::LO,
        ]);

        $childAwardItem = AwardItemEnrolmentHelper::load($this->db, $childItemEnrolId);
        $parent = AwardItemEnrolmentHelper::parent($this->db, $childAwardItem);

        $this->assertEquals($parentItemEnrolmentId, $parent->id);
    }
}
