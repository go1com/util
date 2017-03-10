<?php

namespace go1\util\tests;

use go1\util\EdgeTypes;
use go1\util\EnrolmentHelper;
use go1\util\LoTypes;
use go1\util\schema\mock\EnrolmentMockTrait;
use go1\util\schema\mock\InstanceMockTrait;
use go1\util\schema\mock\LoMockTrait;
use go1\util\schema\mock\UserMockTrait;

class EnrolmentHelperTest extends UtilTestCase
{
    use UserMockTrait;
    use EnrolmentMockTrait;
    use InstanceMockTrait;
    use LoMockTrait;

    protected $instanceName = 'az.mygo1.com';
    protected $profileId    = 11;

    public function setUp()
    {
        parent::setUp();

        // Create instance
        $this->instanceId = $this->createInstance($this->db, ['title' => $this->instanceName]);
        $this->instancePublicKey = $this->createInstancePublicKey($this->db, ['instance' => $this->instanceName]);
        $this->instancePrivateKey = $this->createInstancePrivateKey($this->db, ['instance' => $this->instanceName]);
        $this->userId = $this->createUser($this->db, ['instance' => $this->instanceName]);
        $this->jwt = $this->getJwt();

        $data = json_encode(['elective_number' => 1]);
        $this->lpId = $this->createCourse($this->db, ['type' => 'learning_pathway', 'instance_id' => $this->instanceId]);
        $this->courseId = $this->createCourse($this->db, ['type' => 'course', 'instance_id' => $this->instanceId]);
        $this->moduleId = $this->createCourse($this->db, ['type' => 'module', 'instance_id' => $this->instanceId, 'data' => $data]);
        $this->liVideoId = $this->createCourse($this->db, ['type' => 'video', 'instance_id' => $this->instanceId]);
        $this->liResourceId = $this->createCourse($this->db, ['type' => 'iframe', 'instance_id' => $this->instanceId]);
        $this->electiveQuestionId = $this->createCourse($this->db, ['type' => 'question', 'instance_id' => $this->instanceId]);
        $this->electiveTextId = $this->createCourse($this->db, ['type' => 'text', 'instance_id' => $this->instanceId]);

        // Linking
        $this->link($this->db, EdgeTypes::HAS_LP_ITEM, $this->lpId, $this->courseId, 0);
        $this->link($this->db, EdgeTypes::HAS_MODULE, $this->courseId, $this->moduleId, 0);
        $this->link($this->db, EdgeTypes::HAS_LI, $this->moduleId, $this->liVideoId, 0);
        $this->link($this->db, EdgeTypes::HAS_LI, $this->moduleId, $this->liResourceId, 0);
        $this->link($this->db, EdgeTypes::HAS_ELECTIVE_LI, $this->moduleId, $this->electiveQuestionId, 0);
        $this->link($this->db, EdgeTypes::HAS_ELECTIVE_LI, $this->moduleId, $this->electiveTextId, 0);
    }


    public function testAssessor()
    {
        $enrolmentId = $this->createEnrolment($this->db, ['lo_id' => 1, 'profile_id' => 1]);
        $assessor1Id = $this->createUser($this->db, ['mail' => 'assessor1@mail.com']);
        $assessor2Id = $this->createUser($this->db, ['mail' => 'assessor2@mail.com']);
        $this->createUser($this->db, ['mail' => 'assessor3@mail.com']);

        $this->link($this->db, EdgeTypes::HAS_TUTOR_ENROLMENT_EDGE, $assessor1Id, $enrolmentId);
        $this->link($this->db, EdgeTypes::HAS_TUTOR_ENROLMENT_EDGE, $assessor2Id, $enrolmentId);

        $assessors = EnrolmentHelper::assessorIds($this->db, $enrolmentId);
        $this->assertEquals(2, count($assessors));
        $this->assertEquals($assessor1Id, $assessors[0]);
        $this->assertEquals($assessor2Id, $assessors[1]);
    }

    public function testfindParentEnrolmentNoParentId() {
        $basicLiData = ['profile_id' => $this->profileId, 'taken_instance_id' => $this->instanceId];
        $enrolments  = [
            'lp'       => $this->createEnrolment($this->db, $basicLiData + ['lo_id' => $this->lpId]),
            'course'   => $this->createEnrolment($this->db, $basicLiData + ['lo_id' => $this->courseId]),
            'module'   => $this->createEnrolment($this->db, $basicLiData + ['lo_id' => $this->moduleId]),
            'video'    => $this->createEnrolment($this->db, $basicLiData + ['lo_id' => $this->liVideoId]),
            'resource' => $this->createEnrolment($this->db, $basicLiData + ['lo_id' => $this->liResourceId]),
            'question' => $this->createEnrolment($this->db, $basicLiData + ['lo_id' => $this->electiveQuestionId]),
            'text' => $this->createEnrolment($this->db, $basicLiData + ['lo_id' => $this->electiveTextId]),
        ];

        $course = EnrolmentHelper::findParentEnrolment($this->db, EnrolmentHelper::load($this->db, $enrolments['lp']));
        $this->assertFalse($course);

        $course = EnrolmentHelper::findParentEnrolment($this->db, EnrolmentHelper::load($this->db, $enrolments['module']));
        $this->assertEquals($this->courseId, $course->id);

        $course = EnrolmentHelper::findParentEnrolment($this->db, EnrolmentHelper::load($this->db, $enrolments['video']));
        $this->assertEquals($this->courseId, $course->id);

        $course = EnrolmentHelper::findParentEnrolment($this->db, EnrolmentHelper::load($this->db, $enrolments['resource']));
        $this->assertEquals($this->courseId, $course->id);

        $course = EnrolmentHelper::findParentEnrolment($this->db, EnrolmentHelper::load($this->db, $enrolments['question']));
        $this->assertEquals($this->courseId, $course->id);

        $course = EnrolmentHelper::findParentEnrolment($this->db, EnrolmentHelper::load($this->db, $enrolments['text']));
        $this->assertEquals($this->courseId, $course->id);

        $lp = EnrolmentHelper::findParentEnrolment($this->db, EnrolmentHelper::load($this->db, $enrolments['video']), LoTypes::LEANING_PATHWAY);
        $this->assertEquals($this->lpId, $lp->id);
    }

    public function testfindParentEnrolmentWithParentId() {
        $basicLiData = ['profile_id' => $this->profileId, 'taken_instance_id' => $this->instanceId];
        $enrolments  = [
            'lp'       => $this->createEnrolment($this->db, $basicLiData + ['lo_id' => $this->lpId]),
            'course'   => $this->createEnrolment($this->db, $basicLiData + ['lo_id' => $this->courseId, 'parent_lo_id' => $this->lpId]),
            'module'   => $this->createEnrolment($this->db, $basicLiData + ['lo_id' => $this->moduleId, 'parent_lo_id' => $this->courseId]),
            'video'    => $this->createEnrolment($this->db, $basicLiData + ['lo_id' => $this->liVideoId, 'parent_lo_id' => $this->moduleId]),
            'resource' => $this->createEnrolment($this->db, $basicLiData + ['lo_id' => $this->liResourceId, 'parent_lo_id' => $this->moduleId]),
            'question' => $this->createEnrolment($this->db, $basicLiData + ['lo_id' => $this->electiveQuestionId, 'parent_lo_id' => $this->moduleId]),
            'text' => $this->createEnrolment($this->db, $basicLiData + ['lo_id' => $this->electiveTextId, 'parent_lo_id' => $this->moduleId]),
        ];

        $course = EnrolmentHelper::findParentEnrolment($this->db, EnrolmentHelper::load($this->db, $enrolments['lp']));
        $this->assertFalse($course);

        $course = EnrolmentHelper::findParentEnrolment($this->db, EnrolmentHelper::load($this->db, $enrolments['module']));
        $this->assertEquals($this->courseId, $course->id);

        $course = EnrolmentHelper::findParentEnrolment($this->db, EnrolmentHelper::load($this->db, $enrolments['video']));
        $this->assertEquals($this->courseId, $course->id);

        $course = EnrolmentHelper::findParentEnrolment($this->db, EnrolmentHelper::load($this->db, $enrolments['resource']));
        $this->assertEquals($this->courseId, $course->id);

        $course = EnrolmentHelper::findParentEnrolment($this->db, EnrolmentHelper::load($this->db, $enrolments['question']));
        $this->assertEquals($this->courseId, $course->id);

        $course = EnrolmentHelper::findParentEnrolment($this->db, EnrolmentHelper::load($this->db, $enrolments['text']));
        $this->assertEquals($this->courseId, $course->id);

        $lp = EnrolmentHelper::findParentEnrolment($this->db, EnrolmentHelper::load($this->db, $enrolments['video']), LoTypes::LEANING_PATHWAY);
        $this->assertEquals($this->lpId, $lp->id);
    }
}
