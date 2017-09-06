<?php

namespace go1\util\tests\enrolment;

use go1\util\DateTime;
use go1\util\edge\EdgeHelper;
use go1\util\edge\EdgeTypes;
use go1\util\enrolment\EnrolmentHelper;
use go1\util\enrolment\EnrolmentStatuses;
use go1\util\lo\LoHelper;
use go1\util\lo\LoTypes;
use go1\util\Queue;
use go1\util\schema\mock\EnrolmentMockTrait;
use go1\util\schema\mock\InstanceMockTrait;
use go1\util\schema\mock\LoMockTrait;
use go1\util\schema\mock\UserMockTrait;
use go1\util\tests\UtilTestCase;

class EnrolmentHelperTest extends UtilTestCase
{
    use UserMockTrait;
    use EnrolmentMockTrait;
    use InstanceMockTrait;
    use LoMockTrait;

    protected $instanceId;
    protected $instancePublicKey;
    protected $instancePrivateKey;
    protected $instanceName = 'az.mygo1.com';
    protected $profileId    = 11;
    protected $userId, $jwt;
    protected $lpId, $courseId, $moduleId, $liVideoId, $liResourceId, $liInteractiveId, $electiveQuestionId, $electiveTextId, $electiveQuizId;

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
        $this->liInteractiveId = $this->createCourse($this->db, ['type' => 'interactive', 'instance_id' => $this->instanceId]);
        $this->electiveQuestionId = $this->createCourse($this->db, ['type' => 'question', 'instance_id' => $this->instanceId]);
        $this->electiveTextId = $this->createCourse($this->db, ['type' => 'text', 'instance_id' => $this->instanceId]);
        $this->electiveQuizId = $this->createCourse($this->db, ['type' => 'quiz', 'instance_id' => $this->instanceId]);

        // Linking
        $this->link($this->db, EdgeTypes::HAS_LP_ITEM, $this->lpId, $this->courseId, 0);
        $this->link($this->db, EdgeTypes::HAS_MODULE, $this->courseId, $this->moduleId, 0);
        $this->link($this->db, EdgeTypes::HAS_LI, $this->moduleId, $this->liVideoId, 0);
        $this->link($this->db, EdgeTypes::HAS_ELECTIVE_LI, $this->moduleId, $this->electiveQuestionId, 1);
        $this->link($this->db, EdgeTypes::HAS_ELECTIVE_LI, $this->moduleId, $this->electiveTextId, 2);
        $this->link($this->db, EdgeTypes::HAS_LI, $this->moduleId, $this->liResourceId, 3);
        $this->link($this->db, EdgeTypes::HAS_LI, $this->moduleId, $this->liInteractiveId, 4);
        $this->link($this->db, EdgeTypes::HAS_ELECTIVE_LI, $this->moduleId, $this->electiveQuizId, 5);
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

    public function testFindParentEnrolmentNoParentId()
    {
        $basicLiData = ['profile_id' => $this->profileId, 'taken_instance_id' => $this->instanceId];
        $enrolments = [
            'lp'       => $this->createEnrolment($this->db, $basicLiData + ['lo_id' => $this->lpId]),
            'course'   => $this->createEnrolment($this->db, $basicLiData + ['lo_id' => $this->courseId]),
            'module'   => $this->createEnrolment($this->db, $basicLiData + ['lo_id' => $this->moduleId]),
            'video'    => $this->createEnrolment($this->db, $basicLiData + ['lo_id' => $this->liVideoId]),
            'resource' => $this->createEnrolment($this->db, $basicLiData + ['lo_id' => $this->liResourceId]),
            'question' => $this->createEnrolment($this->db, $basicLiData + ['lo_id' => $this->electiveQuestionId]),
            'text'     => $this->createEnrolment($this->db, $basicLiData + ['lo_id' => $this->electiveTextId]),
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

    public function testFindParentEnrolmentWithParentId()
    {
        $basicLiData = ['profile_id' => $this->profileId, 'taken_instance_id' => $this->instanceId];
        $enrolments = [
            'lp'       => $this->createEnrolment($this->db, $basicLiData + ['lo_id' => $this->lpId]),
            'course'   => $this->createEnrolment($this->db, $basicLiData + ['lo_id' => $this->courseId, 'parent_lo_id' => $this->lpId]),
            'module'   => $this->createEnrolment($this->db, $basicLiData + ['lo_id' => $this->moduleId, 'parent_lo_id' => $this->courseId]),
            'video'    => $this->createEnrolment($this->db, $basicLiData + ['lo_id' => $this->liVideoId, 'parent_lo_id' => $this->moduleId]),
            'resource' => $this->createEnrolment($this->db, $basicLiData + ['lo_id' => $this->liResourceId, 'parent_lo_id' => $this->moduleId]),
            'question' => $this->createEnrolment($this->db, $basicLiData + ['lo_id' => $this->electiveQuestionId, 'parent_lo_id' => $this->moduleId]),
            'text'     => $this->createEnrolment($this->db, $basicLiData + ['lo_id' => $this->electiveTextId, 'parent_lo_id' => $this->moduleId]),
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

    public function testSequenceEnrolmentCompleted()
    {
        $basicLiData = ['profile_id' => $this->profileId, 'taken_instance_id' => $this->instanceId];
        $enrolments = [
            'lp'       => $this->createEnrolment($this->db, $basicLiData + ['lo_id' => $this->lpId]),
            'course'   => $this->createEnrolment($this->db, $basicLiData + ['lo_id' => $this->courseId, 'parent_lo_id' => $this->lpId]),
            'module'   => $this->createEnrolment($this->db, $basicLiData + ['lo_id' => $this->moduleId, 'parent_lo_id' => $this->courseId]),
            'video'    => $this->createEnrolment($this->db, $basicLiData + ['lo_id' => $this->liVideoId, 'parent_lo_id' => $this->moduleId, 'status' => 'completed']),
            'question' => $this->createEnrolment($this->db, $basicLiData + ['lo_id' => $this->electiveQuestionId, 'parent_lo_id' => $this->moduleId]),
            'question' => $this->createEnrolment($this->db, $basicLiData + ['lo_id' => $this->liResourceId, 'parent_lo_id' => $this->moduleId]),
        ];

        $completion = EnrolmentHelper::sequenceEnrolmentCompleted($this->db, $this->electiveTextId, $this->moduleId, LoTypes::MODULE, $this->profileId);
        $this->assertTrue($completion);
        $completion = EnrolmentHelper::sequenceEnrolmentCompleted($this->db, $this->liResourceId, $this->moduleId, LoTypes::MODULE, $this->profileId);
        $this->assertTrue($completion);
        $completion = EnrolmentHelper::sequenceEnrolmentCompleted($this->db, $this->electiveQuizId, $this->moduleId, LoTypes::MODULE, $this->profileId);
        $this->assertTrue($completion);

        $completion = EnrolmentHelper::sequenceEnrolmentCompleted($this->db, $this->liInteractiveId, $this->moduleId, LoTypes::MODULE, $this->profileId);
        $this->assertFalse($completion);

    }

    public function testChildrenProcess()
    {
        $this->courseId;
        $module2 = $this->createCourse($this->db, ['type' => 'module', 'instance_id' => $this->instanceId]);
        $module3 = $this->createCourse($this->db, ['type' => 'module', 'instance_id' => $this->instanceId]);
        $module4 = $this->createCourse($this->db, ['type' => 'module', 'instance_id' => $this->instanceId]);
        $module5 = $this->createCourse($this->db, ['type' => 'module', 'instance_id' => $this->instanceId]);

        $this->link($this->db, EdgeTypes::HAS_MODULE, $this->courseId, $module2, 2);
        $this->link($this->db, EdgeTypes::HAS_MODULE, $this->courseId, $module3, 3);
        $this->link($this->db, EdgeTypes::HAS_MODULE, $this->courseId, $module4, 4);
        $this->link($this->db, EdgeTypes::HAS_MODULE, $this->courseId, $module5, 5);

        $courseEnrolmentId = $this->createEnrolment($this->db, ['profile_id' => $this->profileId, 'lo_id' => $this->courseId]);
        $courseEnrolment = EnrolmentHelper::load($this->db, $courseEnrolmentId);
        $progress = EnrolmentHelper::childrenProgress($this->db, $courseEnrolment);
        $this->assertEquals(5, $progress['total']);

        $basicModuleData = ['profile_id' => $this->profileId, 'taken_instance_id' => $this->instanceId, 'parent_lo_id' => $this->courseId];
        $this->createEnrolment($this->db, $basicModuleData + ['lo_id' => $this->moduleId]);
        $this->createEnrolment($this->db, $basicModuleData + ['lo_id' => $module2]);
        $this->createEnrolment($this->db, $basicModuleData + ['lo_id' => $module3]);

        $progress = EnrolmentHelper::childrenProgress($this->db, $courseEnrolment);
        $this->assertEquals(3, $progress[EnrolmentStatuses::IN_PROGRESS]);
        $this->assertEquals(5, $progress['total']);

        $this->createEnrolment($this->db, $basicModuleData + ['lo_id' => $module4, 'status' => EnrolmentStatuses::COMPLETED]);
        $this->createEnrolment($this->db, $basicModuleData + ['lo_id' => $module5, 'status' => EnrolmentStatuses::COMPLETED]);
        $progress = EnrolmentHelper::childrenProgress($this->db, $courseEnrolment);
        $this->assertEquals(2, $progress[EnrolmentStatuses::COMPLETED]);
        $this->assertEquals(3, $progress[EnrolmentStatuses::IN_PROGRESS]);
        $this->assertEquals(5, $progress['total']);
    }

    public function testCreate()
    {
        $lo = LoHelper::load($this->db, $this->courseId);
        $status = EnrolmentStatuses::NOT_STARTED;
        $date = (new \DateTime())->format('Y-m-d h:i:s');
        EnrolmentHelper::create($this->db, $this->queue, 1, 1, 0, $lo, 1000, $status, $date);

        $e = EnrolmentHelper::load($this->db, 1);
        $this->assertEquals($status, $e->status);

        $message = $this->queueMessages[Queue::ENROLMENT_CREATE];
        $this->assertCount(1, $message);
    }

    public function testCreateWithMarketplaceLO()
    {
        $instanceId = $this->createInstance($this->db, []);
        $courseId = $this->createCourse($this->db, ['instance_id' => $instanceId, 'marketplace' => 1]);
        $lo = LoHelper::load($this->db, $courseId);
        $status = EnrolmentStatuses::NOT_STARTED;
        $date = DateTime::formatDate('now');
        EnrolmentHelper::create($this->db, $this->queue, 1, 1, 0, $lo, 1000, $status, $date);

        $e = EnrolmentHelper::load($this->db, 1);
        $this->assertEquals($status, $e->status);

        $this->assertCount(1, $this->queueMessages[Queue::DO_USER_CREATE_VIRTUAL_ACCOUNT]);
        $this->assertCount(1, $this->queueMessages[Queue::ENROLMENT_CREATE]);
    }
}
