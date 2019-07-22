<?php

namespace go1\util\tests\award;

use go1\util\award\AwardHelper;
use go1\util\award\event_publishing\AwardCreateEventEmbedder;
use go1\util\edge\EdgeTypes;
use go1\util\schema\mock\AwardMockTrait;
use go1\util\schema\mock\PortalMockTrait;
use go1\util\tests\UtilCoreTestCase;
use go1\util\Text;
use Symfony\Component\HttpFoundation\Request;

class AwardCreateEventEmbedderTest extends UtilCoreTestCase
{
    use PortalMockTrait;
    use AwardMockTrait;

    protected $portalId;
    protected $userId;
    protected $accountId;
    protected $jwt;
    protected $awardId;

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
    }

    public function test()
    {
        $c = $this->getContainer();
        $award = AwardHelper::load($this->go1, $this->awardId);
        $embedder = new AwardCreateEventEmbedder($this->go1, $c['access_checker']);
        $req = Request::create('/', 'POST');
        $req->attributes->set('jwt.payload', Text::jwtContent($this->jwt));
        $embedded = $embedder->embedded($award, $req);

        $this->assertEquals('qa.mygo1.com', $embedded['portal']->title);
        $this->assertEquals($this->userId, $embedded['authors'][0]->id);
        $this->assertEquals('A', $embedded['jwt']['user']->first_name);
        $this->assertEquals('T', $embedded['jwt']['user']->last_name);
    }
}
