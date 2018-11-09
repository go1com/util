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

    public function setUp()
    {
        parent::setUp();

        $c = $this->getContainer();
        $this->portalId = $this->createPortal($this->db, ['title' => 'qa.mygo1.com']);
        $this->userId = $this->createUser($this->db, ['instance' => $c['accounts_name']]);
        $this->accountId = $this->createUser($this->db, ['instance' => 'qa.mygo1.com']);
        $this->link($this->db, EdgeTypes::HAS_ACCOUNT, $this->userId, $this->accountId);
        $this->jwt = $this->jwtForUser($this->db, $this->userId, 'qa.mygo1.com');
        $this->awardId = $this->createAward($this->db, ['instance_id' => $this->portalId, 'user_id' => $this->userId]);
        $this->awardEnrolmentId = $this->createAwardEnrolment($this->db, ['instance_id' => $this->portalId, 'user_id' => $this->userId, 'award_id' => $this->awardId]);
    }

    public function test()
    {
        $c = $this->getContainer();
        $awardEnrolment = AwardHelper::loadEnrolment($this->db, $this->awardEnrolmentId);
        $embedder = new AwardEnrolmentCreateEventEmbedder($this->db, $this->db, $c['access_checker']);
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
