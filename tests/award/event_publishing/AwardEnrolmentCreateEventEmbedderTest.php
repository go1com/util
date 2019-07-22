<?php

namespace go1\util\tests\award;

use go1\util\award\AwardHelper;
use go1\util\award\event_publishing\AwardEnrolmentCreateEventEmbedder;
use go1\util\edge\EdgeTypes;
use go1\util\schema\mock\AwardMockTrait;
use go1\util\schema\mock\PortalMockTrait;
use go1\util\tests\UtilCoreTestCase;
use go1\util\Text;
use Symfony\Component\HttpFoundation\Request;

class AwardEnrolmentCreateEventEmbedderTest extends UtilCoreTestCase
{
    use PortalMockTrait;
    use AwardMockTrait;

    protected $portalId;
    protected $userId;
    protected $accountId;
    protected $jwt;
    protected $awardId;
    protected $awardEnrolmentId;

    public function setUp() : void
    {
        parent::setUp();

        $c = $this->getContainer();
        $this->portalId = $this->createPortal($this->go1, ['title' => 'qa.mygo1.com']);
        $this->userId = $this->createUser($this->go1, ['instance' => $c['accounts_name']]);
        $this->accountId = $this->createUser($this->go1, ['instance' => 'qa.mygo1.com']);
        $this->link($this->go1, EdgeTypes::HAS_ACCOUNT, $this->userId, $this->accountId);
        $this->jwt = $this->jwtForUser($this->go1, $this->userId, 'qa.mygo1.com');
        $this->awardId = $this->createAward($this->go1, ['instance_id' => $this->portalId, 'user_id' => $this->userId]);
        $this->awardEnrolmentId = $this->createAwardEnrolment($this->go1, ['instance_id' => $this->portalId, 'user_id' => $this->userId, 'award_id' => $this->awardId]);
    }

    public function test()
    {
        $c = $this->getContainer();
        $awardEnrolment = AwardHelper::loadEnrolment($this->go1, $this->awardEnrolmentId);
        $embedder = new AwardEnrolmentCreateEventEmbedder($this->go1, $this->go1, $c['access_checker']);
        $req = Request::create('/', 'POST');
        $req->attributes->set('jwt.payload', Text::jwtContent($this->jwt));
        $embedded = $embedder->embedded($awardEnrolment, $req);

        $this->assertEquals($this->portalId, $embedded['portal']->id);
        $this->assertEquals('qa.mygo1.com', $embedded['portal']->title);
        $this->assertEquals($this->awardId, $embedded['award']->id);
        $this->assertEquals('Example award', $embedded['award']->title);
        $this->assertEquals($this->accountId, $embedded['account']->id);
    }
}
