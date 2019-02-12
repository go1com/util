<?php

namespace go1\util\tests\lo;

use go1\util\edge\EdgeTypes;
use go1\util\lo\event_publishing\LoCreateEventEmbedder;
use go1\util\lo\LoHelper;
use go1\util\schema\mock\LoMockTrait;
use go1\util\schema\mock\PortalMockTrait;
use go1\util\tests\UtilCoreTestCase;
use go1\util\Text;
use Symfony\Component\HttpFoundation\Request;

class LoCreateEventEmbedderTest extends UtilCoreTestCase
{
    use PortalMockTrait;
    use LoMockTrait;

    protected $portalId;
    protected $userId;
    protected $accountId;
    protected $jwt;
    protected $courseId;
    protected $moduleId;
    protected $eventLiId;

    public function setUp() : void
    {
        parent::setUp();

        $c = $this->getContainer();
        $this->portalId = $this->createPortal($this->go1, ['title' => 'qa.mygo1.com']);
        $this->userId = $this->createUser($this->go1, ['instance' => $c['accounts_name']]);
        $this->accountId = $this->createUser($this->go1, ['instance' => 'qa.mygo1.com']);
        $this->link($this->go1, EdgeTypes::HAS_ACCOUNT, $this->userId, $this->accountId);
        $this->jwt = $this->jwtForUser($this->go1, $this->userId, 'qa.mygo1.com');
        $this->courseId = $this->createCourse($this->go1, ['instance_id' => $this->portalId]);
        $this->moduleId = $this->createModule($this->go1, ['instance_id' => $this->portalId]);
        $this->eventLiId = $this->createLO($this->go1, ['instance_id' => $this->portalId, 'type' => 'event']);
        $this->link($this->go1, EdgeTypes::HAS_MODULE, $this->courseId, $this->moduleId);
        $this->link($this->go1, EdgeTypes::HAS_LI, $this->moduleId, $this->eventLiId);
    }

    public function test()
    {
        $c = $this->getContainer();
        $event = LoHelper::load($this->go1, $this->eventLiId);
        $embedder = new LoCreateEventEmbedder($this->go1, $c['access_checker']);
        $req = Request::create('/', 'POST');
        $req->attributes->set('jwt.payload', Text::jwtContent($this->jwt));
        $embedded = $embedder->embedded($event, $req);

        $this->assertEquals('qa.mygo1.com', $embedded['portal']->title);
        $this->assertEquals('A', $embedded['jwt']['user']->first_name);
        $this->assertEquals('T', $embedded['jwt']['user']->last_name);
    }
}
