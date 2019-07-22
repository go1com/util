<?php

namespace go1\util\tests\award;


use go1\util\award\AwardHelper;
use go1\util\award\AwardItemEnrolmentHelper;
use go1\util\award\AwardItemTypes;
use go1\util\schema\mock\AwardMockTrait;
use go1\util\schema\mock\PortalMockTrait;
use go1\util\schema\mock\UserMockTrait;
use go1\util\tests\UtilCoreTestCase;

class AwardItemEnrolmentTest extends UtilCoreTestCase
{
    use PortalMockTrait;
    use UserMockTrait;
    use AwardMockTrait;

    public function testAwardItemEnrolmentParent()
    {
        $loId = 1001;
        $userId = 1011;

        $portalId =  $this->createPortal($this->go1, ['title' => 'util.mygo1.com']);
        $awardId = $this->createAward($this->go1, ['instance_id' => $portalId]);
        $childAwardId = $this->createAward($this->go1, ['instance_id' => $portalId]);
        $award = AwardHelper::load($this->go1, $awardId);
        $childAward = AwardHelper::load($this->go1, $childAwardId);

        $this->createAwardItem($this->go1, $childAward->revision_id, AwardItemTypes::LO, $loId);
        $childItemEnrolId = $this->createAwardItemEnrolment($this->go1, [
            'award_id'    => $childAwardId,
            'entity_id'   => $loId,
            'user_id'     => $userId,
            'instance_id' => $portalId,
            'type'        => AwardItemTypes::AWARD,
        ]);

        $this->createAwardItem($this->go1, $award->revision_id, AwardItemTypes::AWARD, $childAwardId);
        $parentItemEnrolmentId = $this->createAwardItemEnrolment($this->go1, [
            'award_id'    => $awardId,
            'entity_id'   => $childAwardId,
            'user_id'     => $userId,
            'instance_id' => $portalId,
            'type'        => AwardItemTypes::LO,
        ]);

        $childAwardItem = AwardItemEnrolmentHelper::load($this->go1, $childItemEnrolId);
        $parent = AwardItemEnrolmentHelper::parent($this->go1, $childAwardItem);

        $this->assertEquals($parentItemEnrolmentId, $parent->id);
    }
}
