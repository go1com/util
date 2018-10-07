<?php

namespace go1\util\tests\award;

use go1\util\award\AwardHelper;
use go1\util\award\event_publishing\AwardCreateEventEmbedder;
use go1\util\edge\EdgeTypes;
use go1\util\schema\mock\AwardMockTrait;
use go1\util\schema\mock\PortalMockTrait;
use go1\util\tests\UtilTestCase;
use go1\util\Text;
use Symfony\Component\HttpFoundation\Request;

class AwardCreateEventEmbedderTest extends UtilTestCase
{
    use PortalMockTrait;
    use AwardMockTrait;

    protected $portalId;
    protected $userId;
    protected $accountId;
    protected $jwt;
    protected $awardId;

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
    }

    public function test()
    {
        $c = $this->getContainer();
        $award = AwardHelper::load($this->db, $this->awardId);
        $embedder = new AwardCreateEventEmbedder($this->db, $c['access_checker']);
        $req = Request::create('/', 'POST');
        $req->attributes->set('jwt.payload', Text::jwtContent($this->jwt));
        $embedded = $embedder->embedded($award, $req);

        $this->assertEquals('qa.mygo1.com', $embedded['portal']->title);
        $this->assertEquals($this->userId, $embedded['authors'][0]->id);
        $this->assertEquals('A', $embedded['jwt']['user']->first_name);
        $this->assertEquals('T', $embedded['jwt']['user']->last_name);
    }
}
