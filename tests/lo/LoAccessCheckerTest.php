<?php

namespace go1\util\tests\lo;

use Doctrine\Common\Cache\ArrayCache;
use go1\util\edge\EdgeTypes;
use go1\util\lo\LoAccessChecker;
use go1\util\lo\LoChecker;
use go1\util\portal\PortalChecker;
use go1\util\schema\mock\InstanceMockTrait;
use go1\util\schema\mock\LoMockTrait;
use go1\util\schema\mock\UserMockTrait;
use go1\util\tests\UtilTestCase;
use go1\util\user\Roles;
use Symfony\Component\HttpFoundation\Request;

class LoAccessCheckerTest extends UtilTestCase
{
    use LoMockTrait;
    use InstanceMockTrait;
    use UserMockTrait;

    protected $lpId, $courseId;

    protected $instanceName       = 'az.mygo1.com';
    protected $publicInstanceName = 'public.mygo1.com';
    protected $instanceId, $publicInstanceId;

    protected $tutor  = 'tutor@go1.com';
    protected $author = 'author@go1.com';
    protected $parentAuthor = 'parent.author@go1.com';
    protected $student = 'student@go1.com';
    protected $tutorId, $authorId, $parentAuthorId, $studentId;
    protected $tutorJwt, $authorJwt, $parentAuthorJwt, $studentJwt;
    protected $publicPortalTutorJwt, $publicPortalAuthorJwt, $publicPortalStudentJwt;

    public function setUp()
    {
        parent::setUp();

        $this->instanceId = $this->createInstance($this->db, ['title' => $this->instanceName]);
        $this->publicInstanceId = $this->createInstance($this->db, ['title' => $this->publicInstanceName, 'data' => json_encode(['configuration' => ['public_writing' => 1]])]);

        $this->tutorId           = $this->createUser($this->db, ['mail' => $this->tutor]);
        $this->authorId          = $this->createUser($this->db, ['mail' => $this->author]);
        $this->parentAuthorId    = $this->createUser($this->db, ['mail' => $this->parentAuthor]);
        $this->studentId = $this->createUser($this->db, ['mail' => $this->student]);

        $this->lpId = $this->createLearningPathway($this->db, ['instance_id' => $this->instanceId]);
        $this->courseId = $this->createCourse($this->db, ['instance_id' => $this->instanceId]);
        $this->link($this->db, EdgeTypes::HAS_LP_ITEM, $this->lpId, $this->courseId);

        $this->link($this->db, EdgeTypes::HAS_AUTHOR_EDGE, $this->lpId, $this->parentAuthorId);
        $this->link($this->db, EdgeTypes::HAS_AUTHOR_EDGE, $this->lpId, $this->authorId);
        $this->link($this->db, EdgeTypes::HAS_AUTHOR_EDGE, $this->courseId, $this->authorId);

        $this->tutorJwt   = $this->getJwt($this->tutor, 'accounts.gocatalyze.com', $this->instanceName, [Roles::TUTOR], 1, $this->tutorId);
        $this->authorJwt  = $this->getJwt($this->author, 'accounts.gocatalyze.com', $this->instanceName, [Roles::AUTHENTICATED], 1, $this->authorId);
        $this->parentAuthorJwt = $this->getJwt($this->parentAuthor, 'accounts.gocatalyze.com', $this->instanceName, [Roles::STUDENT], 1, $this->parentAuthorId);
        $this->studentJwt = $this->getJwt($this->student, 'accounts.gocatalyze.com', $this->instanceName, [Roles::STUDENT], 1, $this->studentId);
        $this->publicPortalTutorJwt  = $this->getJwt($this->tutor, 'accounts.gocatalyze.com', $this->publicInstanceName, [Roles::AUTHENTICATED], 1, $this->tutorId);
        $this->publicPortalAuthorJwt  = $this->getJwt($this->author, 'accounts.gocatalyze.com', $this->publicInstanceName, [Roles::AUTHENTICATED], 1, $this->authorId);
        $this->publicPortalStudentJwt = $this->getJwt($this->student, 'accounts.gocatalyze.com', $this->publicInstanceName, [Roles::STUDENT], 1, $this->studentId);
    }

    protected function getLoAccessChecker(PortalChecker $portalChecker = null, LoChecker $loChecker = null)
    {
        $portalChecker = $portalChecker ?: new PortalChecker();
        $loChecker = $loChecker ?: new LoChecker();
        return new LoAccessChecker($this->db, $portalChecker, $loChecker, new ArrayCache);
    }

    public function testInvalidUser()
    {
        $loAccessChecker = self::getLoAccessChecker();
        $request = $loAccessChecker->access('any', new Request(), (object) []);

        $this->assertEquals(403, $request->getStatusCode());
        $this->assertEquals('Invalid user.', json_decode($request->getContent())->message);
    }

    public function testLoCreateWithoutAccount()
    {
        $jwt = $this->getJwt('someone@go1.com', 'accounts.gocatalyze.com', 'xyz.mygo1.com', [Roles::ADMIN]);
        $req = Request::create('/lo', 'POST');
        $req->query->replace(['jwt' => $jwt]);
        $this->middlewarePreProcess($req);

        $options = (object) [
            'type'          => 'course',
            'title'         => 'lo create course test',
            'published'     => 1,
            'instanceId'    => $this->instanceId,
            'instanceName'  => $this->instanceName,
        ];

        $loAccessChecker = self::getLoAccessChecker();
        $res = $loAccessChecker->access('lo.create', $req, $options);

        $this->assertEquals(404, $res->getStatusCode());
        $this->assertEquals('Account not found.', json_decode($res->getContent())->message);
    }

    public function dataLoCreateByPortalTutor()
    {
        self::setUp();
        return [
            [$this->tutorJwt, null],
            [
                $this->studentJwt,
                [
                    'code' => 403,
                    'message' => 'Only admin or tutor or parent learning object\'s author can create learning object in portal.'
                ]
            ]
        ];
    }

    /**
     * @dataProvider dataLoCreateByPortalTutor
     */
    public function testLoCreateByPortalTutor($jwt, $expectedRes)
    {
        $req = Request::create('/lo', 'POST');
        $req->query->replace(['jwt' => $jwt]);
        $this->middlewarePreProcess($req);

        $options = (object) [
            'type'          => 'course',
            'title'         => 'lo create course test',
            'published'     => 1,
            'instanceId'    => $this->instanceId,
            'instanceName'  => $this->instanceName,
            'linkSourceId'  => null,
        ];

        $loAccessChecker = self::getLoAccessChecker();
        $res = $loAccessChecker->access('lo.create', $req, $options);

        if ($expectedRes) {
            $this->assertEquals($expectedRes['code'], $res->getStatusCode());
            $this->assertEquals($expectedRes['message'], json_decode($res->getContent())->message);
        }
        else {
            $this->assertTrue(is_null($res));
        }

    }

    public function testLoCreateOnPublicWritingPortal()
    {
        $req = Request::create('/lo', 'POST');
        $req->query->replace(['jwt' => $this->publicPortalStudentJwt]);
        $this->middlewarePreProcess($req);

        $options = (object) [
            'type'          => 'course',
            'title'         => 'lo create course test',
            'published'     => 1,
            'instanceId'    => $this->publicInstanceId,
            'instanceName'  => $this->publicInstanceName,
            'linkSourceId'  => null,
        ];

        $loAccessChecker = self::getLoAccessChecker();
        $res = $loAccessChecker->access('lo.create', $req, $options);

        $this->assertTrue(is_null($res));
    }

    public function testLoCreateByParentAuthor()
    {
        $req = Request::create('/lo', 'POST');
        $req->query->replace(['jwt' => $this->parentAuthorJwt]);
        $this->middlewarePreProcess($req);

        $options = (object) [
            'type'          => 'course',
            'title'         => 'lo create course test',
            'published'     => 1,
            'instanceId'    => $this->instanceId,
            'instanceName'  => $this->instanceName,
            'linkSourceId'  => $this->lpId,
        ];

        $loAccessChecker = self::getLoAccessChecker();
        $res = $loAccessChecker->access('lo.create', $req, $options);

        $this->assertTrue(is_null($res));
    }

    public function testLoCreateByGrandParentAuthor()
    {
        $req = Request::create('/lo', 'POST');
        $req->query->replace(['jwt' => $this->parentAuthorJwt]);
        $this->middlewarePreProcess($req);

        $options = (object) [
            'type'          => 'module',
            'title'         => 'lo create course test',
            'published'     => 1,
            'instanceId'    => $this->instanceId,
            'instanceName'  => $this->instanceName,
            'linkSourceId'  => $this->courseId,
        ];

        $loAccessChecker = self::getLoAccessChecker();
        $res = $loAccessChecker->access('lo.create', $req, $options);

        $this->assertTrue(is_null($res));
    }
}
