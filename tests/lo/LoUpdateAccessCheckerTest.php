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

class LoUpdateAccessCheckerTest extends LoAccessCheckerTest
{
    use LoMockTrait;
    use InstanceMockTrait;
    use UserMockTrait;

    private $publicCourseId, $moduleId;

    public function setUp()
    {
        parent::setUp();

        $this->publicCourseId = $this->createCourse($this->db, ['instance_id' => $this->publicInstanceId]);
        $this->link($this->db, EdgeTypes::HAS_AUTHOR_EDGE, $this->publicCourseId, $this->publicPortalAuthorJwt);

        $this->moduleId = $this->createModule($this->db, ['instance_id' => $this->instanceId]);
        $this->link($this->db, EdgeTypes::HAS_MODULE, $this->courseId, $this->moduleId);
    }

    public function testLoUpdateWithoutAccount()
    {
        $jwt = $this->getJwt('someone@go1.com', 'accounts.gocatalyze.com', 'xyz.mygo1.com', [Roles::ADMIN]);
        $req = Request::create('/lo', 'POST');
        $req->query->replace(['jwt' => $jwt]);
        $this->middlewarePreProcess($req);

        $options = (object) [
            'id'            => $this->courseId,
            'title'         => 'lo update course test',
            'instanceId'    => $this->instanceId,
            'instanceName'  => $this->instanceName,
        ];

        $loAccessChecker = self::getLoAccessChecker();
        $res = $loAccessChecker->access('lo.update', $req, $options);

        $this->assertEquals(404, $res->getStatusCode());
        $this->assertEquals('Account not found.', json_decode($res->getContent())->message);
    }

    public function dataLoUpdateByPortalTutorOrAuthor()
    {
        self::setUp();
        return [
            [$this->tutorJwt, null],
            [$this->authorJwt, null],
            [
                $this->studentJwt,
                [
                    'code' => 403,
                    'message' => 'Only admin or tutor or parent learning object\'s author can update learning object in portal.'
                ]
            ]
        ];
    }

    /**
     * @dataProvider dataLoUpdateByPortalTutorOrAuthor
     */
    public function testLoUpdateByPortalTutorOrAuthor($jwt, $expectedRes)
    {
        $req = Request::create('/lo', 'POST');
        $req->query->replace(['jwt' => $jwt]);
        $this->middlewarePreProcess($req);

        $options = (object) [
            'id'            => $this->courseId,
            'title'         => 'lo update course test',
            'instanceId'    => $this->instanceId,
            'instanceName'  => $this->instanceName,
        ];

        $loAccessChecker = self::getLoAccessChecker();
        $res = $loAccessChecker->access('lo.update', $req, $options);

        if ($expectedRes) {
            $this->assertEquals($expectedRes['code'], $res->getStatusCode());
            $this->assertEquals($expectedRes['message'], json_decode($res->getContent())->message);
        }
        else {
            $this->assertTrue(is_null($res));
        }

    }

    public function dataLoUpdateParentByPortalTutorOrAuthor()
    {
        self::setUp();
        return [
            [$this->tutorJwt, null],
            [$this->authorJwt, null],
            [
                $this->studentJwt,
                [
                    'code' => 403,
                    'message' => 'Only admin or tutor or parent learning object\'s author can update learning object in portal.'
                ]
            ],
            [
                $this->publicPortalTutorJwt,
                [
                    'code' => 404,
                    'message' => 'Account not found.'
                ]
            ],
            [
                $this->publicPortalAuthorJwt,
                [
                    'code' => 404,
                    'message' => 'Account not found.'
                ]
            ],
            [
                $this->studentJwt,
                [
                    'code' => 403,
                    'message' => 'Only admin or tutor or parent learning object\'s author can update learning object in portal.'
                ]
            ]
        ];
    }

    /**
     * @dataProvider dataLoUpdateParentByPortalTutorOrAuthor
     */
    public function testLoUpdateParentByPortalTutorOrAuthor($jwt, $expectedRes)
    {
        $req = Request::create('/lo', 'POST');
        $req->query->replace(['jwt' => $jwt]);
        $this->middlewarePreProcess($req);

        $options = (object) [
            'id'            => $this->courseId,
            'title'         => 'lo update course test',
            'instanceId'    => $this->instanceId,
            'instanceName'  => $this->instanceName,
            'linkSourceId'  => $this->publicInstanceName,
        ];

        $loAccessChecker = self::getLoAccessChecker();
        $res = $loAccessChecker->access('lo.update', $req, $options);

        if ($expectedRes) {
            $this->assertEquals($expectedRes['code'], $res->getStatusCode());
            $this->assertEquals($expectedRes['message'], json_decode($res->getContent())->message);
        }
        else {
            $this->assertTrue(is_null($res));
        }

    }

    public function testLoCreateByParentAuthor()
    {
        $req = Request::create('/lo', 'POST');
        $req->query->replace(['jwt' => $this->parentAuthorJwt]);
        $this->middlewarePreProcess($req);

        $options = (object) [
            'id'            => $this->courseId,
            'title'         => 'lo update course test',
            'instanceId'    => $this->instanceId,
            'instanceName'  => $this->instanceName,
        ];

        $loAccessChecker = self::getLoAccessChecker();
        $res = $loAccessChecker->access('lo.update', $req, $options);

        $this->assertTrue(is_null($res));
    }

    public function testLoCreateByGrandParentAuthor()
    {
        $req = Request::create('/lo', 'POST');
        $req->query->replace(['jwt' => $this->parentAuthorJwt]);
        $this->middlewarePreProcess($req);

        $options = (object) [
            'id'            => $this->moduleId,
            'title'         => 'lo update module test',
            'instanceId'    => $this->instanceId,
            'instanceName'  => $this->instanceName,
        ];

        $loAccessChecker = self::getLoAccessChecker();
        $res = $loAccessChecker->access('lo.update', $req, $options);

        $this->assertTrue(is_null($res));
    }
}
