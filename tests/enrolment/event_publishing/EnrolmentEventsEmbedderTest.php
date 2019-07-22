<?php

namespace go1\util\tests\enrolment\event_publishing;

use go1\util\edge\EdgeTypes;
use go1\util\enrolment\event_publishing\EnrolmentEventsEmbedder;
use go1\util\enrolment\EnrolmentHelper;
use go1\util\schema\mock\EnrolmentMockTrait;
use go1\util\schema\mock\LoMockTrait;
use go1\util\schema\mock\PortalMockTrait;
use go1\util\tests\UtilCoreTestCase;
use go1\util\Text;
use Symfony\Component\HttpFoundation\Request;

class EnrolmentEventsEmbedderTest extends UtilCoreTestCase
{
    use PortalMockTrait;
    use LoMockTrait;
    use EnrolmentMockTrait;

    protected $portalId;
    protected $userId;
    protected $accountId;
    protected $profileId = 999;
    protected $jwt;
    protected $courseId;
    protected $enrolmentId;

    public function setUp() : void
    {
        parent::setUp();

        $c = $this->getContainer();
        $this->portalId = $this->createPortal($this->go1, ['title' => 'qa.mygo1.com']);
        $this->userId = $this->createUser($this->go1, ['instance' => $c['accounts_name'], 'profile_id' => $this->profileId]);
        $this->accountId = $this->createUser($this->go1, ['instance' => 'qa.mygo1.com', 'profile_id' => $this->profileId]);
        $this->link($this->go1, EdgeTypes::HAS_ACCOUNT, $this->userId, $this->accountId);
        $this->jwt = $this->jwtForUser($this->go1, $this->userId, 'qa.mygo1.com');
        $this->courseId = $this->createCourse($this->go1, ['instance_id' => $this->portalId]);
        $this->enrolmentId = $this->createEnrolment($this->go1, [
            'lo_id'             => $this->courseId,
            'taken_instance_id' => $this->portalId,
            'profile_id'        => $this->profileId,
        ]);
    }

    public function test()
    {
        $c = $this->getContainer();
        $embedder = new EnrolmentEventsEmbedder($this->go1, $c['access_checker']);
        $enrolment = EnrolmentHelper::load($this->go1, $this->enrolmentId);
        $req = Request::create('/', 'POST');
        $req->attributes->set('jwt.payload', Text::jwtContent($this->jwt));
        $embedded = $embedder->embedded($enrolment, $req);

        $this->assertEquals('qa.mygo1.com', $embedded['portal']->title);
        $this->assertEquals($this->profileId, $embedded['account']->profile_id);
        $this->assertEquals('course', $embedded['lo']->type);
    }
}
