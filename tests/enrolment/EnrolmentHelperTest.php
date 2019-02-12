<?php

namespace go1\util\tests\enrolment;

use go1\util\AccessChecker;
use go1\util\DateTime;
use go1\util\edge\EdgeTypes;
use go1\util\enrolment\EnrolmentHelper;
use go1\util\enrolment\EnrolmentStatuses;
use go1\util\enrolment\event_publishing\EnrolmentEventsEmbedder;
use go1\util\lo\LiTypes;
use go1\util\lo\LoHelper;
use go1\util\lo\LoTypes;
use go1\util\model\Enrolment;
use go1\util\plan\PlanHelper;
use go1\util\plan\PlanTypes;
use go1\util\queue\Queue;
use go1\util\schema\mock\EnrolmentMockTrait;
use go1\util\schema\mock\LoMockTrait;
use go1\util\schema\mock\PlanMockTrait;
use go1\util\schema\mock\PortalMockTrait;
use go1\util\schema\mock\UserMockTrait;
use go1\util\tests\UtilCoreTestCase;

class EnrolmentHelperTest extends UtilCoreTestCase
{
    use UserMockTrait;
    use EnrolmentMockTrait;
    use PlanMockTrait;
    use PortalMockTrait;
    use LoMockTrait;

    protected $portalId;
    protected $portalPublicKey;
    protected $portalPrivateKey;
    protected $portalName = 'az.mygo1.com';
    protected $profileId  = 11;
    protected $userId, $jwt;
    protected $lpId, $courseId, $moduleId, $liVideoId, $liResourceId, $liInteractiveId, $electiveQuestionId, $electiveTextId, $electiveQuizId;

    /**
     * @var EnrolmentEventsEmbedder
     */
    protected $enrolmentEventsEmbedder;

    public function setUp() : void
    {
        parent::setUp();
        $this->enrolmentEventsEmbedder = new EnrolmentEventsEmbedder($this->go1, new AccessChecker);

        // Create instance
        $this->portalId = $this->createPortal($this->go1, ['title' => $this->portalName]);
        $this->portalPublicKey = $this->createPortalPublicKey($this->go1, ['instance' => $this->portalName]);
        $this->portalPrivateKey = $this->createPortalPrivateKey($this->go1, ['instance' => $this->portalName]);
        $this->userId = $this->createUser($this->go1, ['instance' => $this->portalName]);
        $this->jwt = $this->getJwt();

        $data = json_encode(['elective_number' => 1]);
        $this->lpId = $this->createCourse($this->go1, ['type' => 'learning_pathway', 'instance_id' => $this->portalId]);
        $this->courseId = $this->createCourse($this->go1, ['type' => 'course', 'instance_id' => $this->portalId]);
        $this->moduleId = $this->createCourse($this->go1, ['type' => 'module', 'instance_id' => $this->portalId, 'data' => $data]);
        $this->liVideoId = $this->createCourse($this->go1, ['type' => 'video', 'instance_id' => $this->portalId]);
        $this->liResourceId = $this->createCourse($this->go1, ['type' => 'iframe', 'instance_id' => $this->portalId]);
        $this->liInteractiveId = $this->createCourse($this->go1, ['type' => 'interactive', 'instance_id' => $this->portalId]);
        $this->electiveQuestionId = $this->createCourse($this->go1, ['type' => 'question', 'instance_id' => $this->portalId]);
        $this->electiveTextId = $this->createCourse($this->go1, ['type' => 'text', 'instance_id' => $this->portalId]);
        $this->electiveQuizId = $this->createCourse($this->go1, ['type' => 'quiz', 'instance_id' => $this->portalId]);

        // Linking
        $this->link($this->go1, EdgeTypes::HAS_LP_ITEM, $this->lpId, $this->courseId, 0);
        $this->link($this->go1, EdgeTypes::HAS_MODULE, $this->courseId, $this->moduleId, 0);
        $this->link($this->go1, EdgeTypes::HAS_LI, $this->moduleId, $this->liVideoId, 0);
        $this->link($this->go1, EdgeTypes::HAS_ELECTIVE_LI, $this->moduleId, $this->electiveQuestionId, 1);
        $this->link($this->go1, EdgeTypes::HAS_ELECTIVE_LI, $this->moduleId, $this->electiveTextId, 2);
        $this->link($this->go1, EdgeTypes::HAS_LI, $this->moduleId, $this->liResourceId, 3);
        $this->link($this->go1, EdgeTypes::HAS_LI, $this->moduleId, $this->liInteractiveId, 4);
        $this->link($this->go1, EdgeTypes::HAS_ELECTIVE_LI, $this->moduleId, $this->electiveQuizId, 5);
    }

    public function testAssessor()
    {
        $enrolmentId = $this->createEnrolment($this->go1, ['lo_id' => 1, 'profile_id' => 1]);
        $assessor1Id = $this->createUser($this->go1, ['mail' => 'assessor1@mail.com']);
        $assessor2Id = $this->createUser($this->go1, ['mail' => 'assessor2@mail.com']);
        $this->createUser($this->go1, ['mail' => 'assessor3@mail.com']);

        $this->link($this->go1, EdgeTypes::HAS_TUTOR_ENROLMENT_EDGE, $assessor1Id, $enrolmentId);
        $this->link($this->go1, EdgeTypes::HAS_TUTOR_ENROLMENT_EDGE, $assessor2Id, $enrolmentId);

        $assessors = EnrolmentHelper::assessorIds($this->go1, $enrolmentId);
        $this->assertEquals(2, count($assessors));
        $this->assertEquals($assessor1Id, $assessors[0]);
        $this->assertEquals($assessor2Id, $assessors[1]);
    }

    public function testFindParentEnrolmentNoParentId()
    {
        $basicLiData = ['profile_id' => $this->profileId, 'taken_instance_id' => $this->portalId];
        $enrolments = [
            'lp'       => $this->createEnrolment($this->go1, $basicLiData + ['lo_id' => $this->lpId]),
            'course'   => $this->createEnrolment($this->go1, $basicLiData + ['lo_id' => $this->courseId]),
            'module'   => $this->createEnrolment($this->go1, $basicLiData + ['lo_id' => $this->moduleId]),
            'video'    => $this->createEnrolment($this->go1, $basicLiData + ['lo_id' => $this->liVideoId]),
            'resource' => $this->createEnrolment($this->go1, $basicLiData + ['lo_id' => $this->liResourceId]),
            'question' => $this->createEnrolment($this->go1, $basicLiData + ['lo_id' => $this->electiveQuestionId]),
            'text'     => $this->createEnrolment($this->go1, $basicLiData + ['lo_id' => $this->electiveTextId]),
        ];

        $course = EnrolmentHelper::findParentEnrolment($this->go1, EnrolmentHelper::load($this->go1, $enrolments['lp']));
        $this->assertFalse($course);

        $course = EnrolmentHelper::findParentEnrolment($this->go1, EnrolmentHelper::load($this->go1, $enrolments['module']));
        $this->assertEquals($this->courseId, $course->id);

        $course = EnrolmentHelper::findParentEnrolment($this->go1, EnrolmentHelper::load($this->go1, $enrolments['video']));
        $this->assertEquals($this->courseId, $course->id);

        $course = EnrolmentHelper::findParentEnrolment($this->go1, EnrolmentHelper::load($this->go1, $enrolments['resource']));
        $this->assertEquals($this->courseId, $course->id);

        $course = EnrolmentHelper::findParentEnrolment($this->go1, EnrolmentHelper::load($this->go1, $enrolments['question']));
        $this->assertEquals($this->courseId, $course->id);

        $course = EnrolmentHelper::findParentEnrolment($this->go1, EnrolmentHelper::load($this->go1, $enrolments['text']));
        $this->assertEquals($this->courseId, $course->id);

        $lp = EnrolmentHelper::findParentEnrolment($this->go1, EnrolmentHelper::load($this->go1, $enrolments['video']), LoTypes::LEANING_PATHWAY);
        $this->assertEquals($this->lpId, $lp->id);
    }

    public function testFindParentEnrolmentWithParentId()
    {
        $basicLiData = ['profile_id' => $this->profileId, 'taken_instance_id' => $this->portalId];
        $enrolments = [
            'lp'       => $this->createEnrolment($this->go1, $basicLiData + ['lo_id' => $this->lpId]),
            'course'   => $this->createEnrolment($this->go1, $basicLiData + ['lo_id' => $this->courseId, 'parent_lo_id' => $this->lpId]),
            'module'   => $this->createEnrolment($this->go1, $basicLiData + ['lo_id' => $this->moduleId, 'parent_lo_id' => $this->courseId]),
            'video'    => $this->createEnrolment($this->go1, $basicLiData + ['lo_id' => $this->liVideoId, 'parent_lo_id' => $this->moduleId]),
            'resource' => $this->createEnrolment($this->go1, $basicLiData + ['lo_id' => $this->liResourceId, 'parent_lo_id' => $this->moduleId]),
            'question' => $this->createEnrolment($this->go1, $basicLiData + ['lo_id' => $this->electiveQuestionId, 'parent_lo_id' => $this->moduleId]),
            'text'     => $this->createEnrolment($this->go1, $basicLiData + ['lo_id' => $this->electiveTextId, 'parent_lo_id' => $this->moduleId]),
        ];

        $course = EnrolmentHelper::findParentEnrolment($this->go1, EnrolmentHelper::load($this->go1, $enrolments['lp']));
        $this->assertFalse($course);

        $course = EnrolmentHelper::findParentEnrolment($this->go1, EnrolmentHelper::load($this->go1, $enrolments['module']));
        $this->assertEquals($this->courseId, $course->id);

        $course = EnrolmentHelper::findParentEnrolment($this->go1, EnrolmentHelper::load($this->go1, $enrolments['video']));
        $this->assertEquals($this->courseId, $course->id);

        $course = EnrolmentHelper::findParentEnrolment($this->go1, EnrolmentHelper::load($this->go1, $enrolments['resource']));
        $this->assertEquals($this->courseId, $course->id);

        $course = EnrolmentHelper::findParentEnrolment($this->go1, EnrolmentHelper::load($this->go1, $enrolments['question']));
        $this->assertEquals($this->courseId, $course->id);

        $course = EnrolmentHelper::findParentEnrolment($this->go1, EnrolmentHelper::load($this->go1, $enrolments['text']));
        $this->assertEquals($this->courseId, $course->id);

        $lp = EnrolmentHelper::findParentEnrolment($this->go1, EnrolmentHelper::load($this->go1, $enrolments['video']), LoTypes::LEANING_PATHWAY);
        $this->assertEquals($this->lpId, $lp->id);
    }

    public function testSequenceEnrolmentCompleted()
    {
        $basicLiData = ['profile_id' => $this->profileId, 'taken_instance_id' => $this->portalId];
        $enrolments = [
            'lp'       => $this->createEnrolment($this->go1, $basicLiData + ['lo_id' => $this->lpId]),
            'course'   => $this->createEnrolment($this->go1, $basicLiData + ['lo_id' => $this->courseId, 'parent_lo_id' => $this->lpId]),
            'module'   => $this->createEnrolment($this->go1, $basicLiData + ['lo_id' => $this->moduleId, 'parent_lo_id' => $this->courseId]),
            'video'    => $this->createEnrolment($this->go1, $basicLiData + ['lo_id' => $this->liVideoId, 'parent_lo_id' => $this->moduleId, 'status' => 'completed']),
            'question' => $this->createEnrolment($this->go1, $basicLiData + ['lo_id' => $this->electiveQuestionId, 'parent_lo_id' => $this->moduleId]),
            'question' => $this->createEnrolment($this->go1, $basicLiData + ['lo_id' => $this->liResourceId, 'parent_lo_id' => $this->moduleId]),
        ];

        $completion = EnrolmentHelper::sequenceEnrolmentCompleted($this->go1, $this->electiveTextId, $this->moduleId, LoTypes::MODULE, $this->profileId);
        $this->assertTrue($completion);
        $completion = EnrolmentHelper::sequenceEnrolmentCompleted($this->go1, $this->liResourceId, $this->moduleId, LoTypes::MODULE, $this->profileId);
        $this->assertTrue($completion);
        $completion = EnrolmentHelper::sequenceEnrolmentCompleted($this->go1, $this->electiveQuizId, $this->moduleId, LoTypes::MODULE, $this->profileId);
        $this->assertTrue($completion);

        $completion = EnrolmentHelper::sequenceEnrolmentCompleted($this->go1, $this->liInteractiveId, $this->moduleId, LoTypes::MODULE, $this->profileId);
        $this->assertFalse($completion);
    }

    public function testChildrenProgressCount()
    {
        $this->courseId;
        $module2 = $this->createCourse($this->go1, ['type' => 'module', 'instance_id' => $this->portalId]);
        $module3 = $this->createCourse($this->go1, ['type' => 'module', 'instance_id' => $this->portalId]);
        $module4 = $this->createCourse($this->go1, ['type' => 'module', 'instance_id' => $this->portalId]);
        $module5 = $this->createCourse($this->go1, ['type' => 'module', 'instance_id' => $this->portalId]);
        $this->link($this->go1, EdgeTypes::HAS_MODULE, $this->courseId, $module2, 2);
        $this->link($this->go1, EdgeTypes::HAS_MODULE, $this->courseId, $module3, 3);
        $this->link($this->go1, EdgeTypes::HAS_MODULE, $this->courseId, $module4, 4);
        $this->link($this->go1, EdgeTypes::HAS_MODULE, $this->courseId, $module5, 5);
        $courseEnrolmentId = $this->createEnrolment($this->go1, ['profile_id' => $this->profileId, 'lo_id' => $this->courseId]);
        $courseEnrolment = EnrolmentHelper::loadSingle($this->go1, $courseEnrolmentId);
        $progress = EnrolmentHelper::childrenProgressCount($this->go1, $courseEnrolment);
        $this->assertEquals(5, $progress['total']);
        $basicModuleData = ['profile_id' => $this->profileId, 'taken_instance_id' => $this->portalId, 'parent_lo_id' => $this->courseId];
        $this->createEnrolment($this->go1, $basicModuleData + ['lo_id' => $this->moduleId]);
        $this->createEnrolment($this->go1, $basicModuleData + ['lo_id' => $module2]);
        $this->createEnrolment($this->go1, $basicModuleData + ['lo_id' => $module3]);
        $progress = EnrolmentHelper::childrenProgressCount($this->go1, $courseEnrolment);
        $this->assertEquals(3, $progress[EnrolmentStatuses::IN_PROGRESS]);
        $this->assertEquals(5, $progress['total']);
        $this->createEnrolment($this->go1, $basicModuleData + ['lo_id' => $module4, 'status' => EnrolmentStatuses::COMPLETED]);
        $this->createEnrolment($this->go1, $basicModuleData + ['lo_id' => $module5, 'status' => EnrolmentStatuses::COMPLETED]);
        $progress = EnrolmentHelper::childrenProgressCount($this->go1, $courseEnrolment);
        $this->assertEquals(2, $progress[EnrolmentStatuses::COMPLETED]);
        $this->assertEquals(3, $progress[EnrolmentStatuses::IN_PROGRESS]);
        $this->assertEquals(40, $progress[EnrolmentStatuses::PERCENTAGE]);
        $this->assertEquals(5, $progress['total']);
    }

    public function testLearningItemProgressCount()
    {
        $course1 = $this->createCourse($this->go1, ['instance_id' => $this->portalId]);
        $module2 = $this->createCourse($this->go1, ['type' => 'module', 'instance_id' => $this->portalId]);
        $module3 = $this->createCourse($this->go1, ['type' => 'module', 'instance_id' => $this->portalId]);
        $learningItemIds = [
            $this->createCourse($this->go1, ['type' => LiTypes::DOCUMENT, 'instance_id' => $this->portalId]),
            $this->createCourse($this->go1, ['type' => LiTypes::DOCUMENT, 'instance_id' => $this->portalId]),
            $this->createCourse($this->go1, ['type' => LiTypes::DOCUMENT, 'instance_id' => $this->portalId]),
            $this->createCourse($this->go1, ['type' => LiTypes::EVENT, 'instance_id' => $this->portalId]),
            $this->createCourse($this->go1, ['type' => LiTypes::EVENT, 'instance_id' => $this->portalId]),
            $this->createCourse($this->go1, ['type' => LiTypes::EVENT, 'instance_id' => $this->portalId]),
        ];
        $courseEvent = $this->createCourse($this->go1, ['type' => LiTypes::EVENT, 'instance_id' => $this->portalId]);
        $this->link($this->go1, EdgeTypes::HAS_MODULE, $course1, $module2, 2);
        $this->link($this->go1, EdgeTypes::HAS_MODULE, $course1, $module3, 3);
        $this->link($this->go1, EdgeTypes::HAS_LI, $course1, $courseEvent, 3);
        foreach ($learningItemIds as $key => $learningItemId) {
            $this->link($this->go1, EdgeTypes::HAS_LI, $module2, $learningItemId, $key);
        }
        $courseEnrolmentId = $this->createEnrolment($this->go1, ['profile_id' => $this->profileId, 'lo_id' => $course1]);
        $courseEnrolment = EnrolmentHelper::loadSingle($this->go1, $courseEnrolmentId);
        $progress = EnrolmentHelper::childrenProgressCount($this->go1, $courseEnrolment, true, LiTypes::all());
        $this->assertEquals(7, $progress['total']);
        $basicLiData = ['profile_id' => $this->profileId, 'taken_instance_id' => $this->portalId, 'parent_lo_id' => $module2];
        $this->createEnrolment($this->go1, $basicLiData + ['lo_id' => $learningItemIds[0]]);
        $this->createEnrolment($this->go1, $basicLiData + ['lo_id' => $learningItemIds[5]]);
        $this->createEnrolment($this->go1, $basicLiData + ['lo_id' => $learningItemIds[3]]);
        $progress = EnrolmentHelper::childrenProgressCount($this->go1, $courseEnrolment, true, LiTypes::all());
        $this->assertEquals(3, $progress[EnrolmentStatuses::IN_PROGRESS]);
        $this->assertEquals(7, $progress['total']);
        $this->createEnrolment($this->go1, $basicLiData + ['lo_id' => $courseEvent, 'status' => EnrolmentStatuses::COMPLETED]);
        $progress = EnrolmentHelper::childrenProgressCount($this->go1, $courseEnrolment, true, LiTypes::all());
        $this->assertEquals(3, $progress[EnrolmentStatuses::IN_PROGRESS]);
        $this->assertEquals(1, $progress[EnrolmentStatuses::COMPLETED]);
        $this->assertEquals(14, $progress[EnrolmentStatuses::PERCENTAGE]);
        $this->assertEquals(7, $progress['total']);
    }

    public function testCreate()
    {
        $lo = LoHelper::load($this->go1, $this->courseId);
        $enrolment = Enrolment::create();
        $enrolment->id = 1;
        $enrolment->profileId = 1;
        $enrolment->parentLoId = 2;
        $enrolment->parentEnrolmentId = 3;
        $enrolment->takenPortalId = 4;
        $enrolment->status = $status = EnrolmentStatuses::EXPIRED;
        $enrolment->startDate = 5;
        $enrolment->endDate = 6;
        $enrolment->result = 7;
        $enrolment->pass = 1;
        $enrolment->changed = 6;
        $enrolment->data = ['foo' => 'bar'];

        EnrolmentHelper::create($this->go1, $this->queue, $enrolment, $lo, $this->enrolmentEventsEmbedder, null, true);

        $e = EnrolmentHelper::loadSingle($this->go1, 1);
        $this->assertEquals($status, $enrolment->status);
        $this->assertEquals($e->profileId, $enrolment->profileId);
        $this->assertEquals($e->loId, $lo->id);
        $this->assertEquals($e->parentLoId, $enrolment->parentLoId);
        $this->assertEquals($e->parentEnrolmentId, $enrolment->parentEnrolmentId);
        $this->assertEquals($e->startDate, $enrolment->startDate);
        $this->assertEquals($e->endDate, $enrolment->endDate);
        $this->assertEquals($e->result, $enrolment->result);
        $this->assertEquals($e->pass, $enrolment->pass);
        $this->assertEquals($e->changed, $enrolment->changed);
        $this->assertEquals('bar', $e->data->foo);

        $message = $this->queueMessages[Queue::ENROLMENT_CREATE];
        $this->assertCount(1, $message);
        $this->assertTrue($message[0]['_context']['notify_email']);
        $this->assertNull($message[0]['_context']['actor_id']);
        $this->assertArrayHasKey('lo', $message[0]['embedded']);
    }

    public function testCreateWithMarketplaceLO()
    {
        $instanceId = $this->createPortal($this->go1, []);
        $courseId = $this->createCourse($this->go1, ['instance_id' => $instanceId, 'marketplace' => 1]);
        $lo = LoHelper::load($this->go1, $courseId);

        $enrolment = Enrolment::create();
        $enrolment->id = 1;
        $enrolment->profileId = 1;
        $enrolment->parentLoId = 2;
        $enrolment->parentEnrolmentId = 3;
        $enrolment->takenPortalId = 4;
        $enrolment->status = $status = EnrolmentStatuses::EXPIRED;
        $enrolment->startDate = 5;
        $enrolment->endDate = 6;
        $enrolment->result = 7;
        $enrolment->pass = 1;
        $enrolment->changed = 6;
        $enrolment->data = ['foo' => 'bar'];

        EnrolmentHelper::create($this->go1, $this->queue, $enrolment, $lo, $this->enrolmentEventsEmbedder, null, true);

        $e = EnrolmentHelper::loadSingle($this->go1, 1);
        $this->assertEquals($status, $enrolment->status);
        $this->assertEquals($e->profileId, $enrolment->profileId);
        $this->assertEquals($e->loId, $lo->id);
        $this->assertEquals($e->parentLoId, $enrolment->parentLoId);
        $this->assertEquals($e->parentEnrolmentId, $enrolment->parentEnrolmentId);
        $this->assertEquals($e->startDate, $enrolment->startDate);
        $this->assertEquals($e->endDate, $enrolment->endDate);
        $this->assertEquals($e->result, $enrolment->result);
        $this->assertEquals($e->pass, $enrolment->pass);
        $this->assertEquals($e->changed, $enrolment->changed);
        $this->assertEquals('bar', $e->data->foo);

        $this->assertCount(1, $this->queueMessages[Queue::DO_USER_CREATE_VIRTUAL_ACCOUNT]);
        $this->assertCount(1, $this->queueMessages[Queue::ENROLMENT_CREATE]);
    }

    public function testLoadRevision()
    {
        $instanceId = $this->createPortal($this->go1, []);
        $courseId = $this->createCourse($this->go1, ['instance_id' => $instanceId]);
        $status = EnrolmentStatuses::NOT_STARTED;
        $date = DateTime::formatDate('now');
        $this->createRevisionEnrolment($this->go1, [
            'lo_id'        => $courseId,
            'status'       => $status,
            'start_date'   => $date,
            'enrolment_id' => 1000,
        ]);

        $revisionEnrolment = EnrolmentHelper::loadRevision($this->go1, 1000);

        $this->assertEquals($courseId, $revisionEnrolment->lo_id);
        $this->assertEquals($status, $revisionEnrolment->status);
        $this->assertEquals($date, $revisionEnrolment->start_date);
    }

    public function testCountUserEnrolment()
    {
        $basicLiData = ['profile_id' => $this->profileId, 'taken_instance_id' => $this->portalId];
        $enrolments = [
            'lp'       => $this->createEnrolment($this->go1, $basicLiData + ['lo_id' => $this->lpId]),
            'course'   => $this->createEnrolment($this->go1, $basicLiData + ['lo_id' => $this->courseId, 'parent_lo_id' => $this->lpId]),
            'module'   => $this->createEnrolment($this->go1, $basicLiData + ['lo_id' => $this->moduleId, 'parent_lo_id' => $this->courseId]),
            'video'    => $this->createEnrolment($this->go1, $basicLiData + ['lo_id' => $this->liVideoId, 'parent_lo_id' => $this->moduleId]),
            'resource' => $this->createEnrolment($this->go1, $basicLiData + ['lo_id' => $this->liResourceId, 'parent_lo_id' => $this->moduleId]),
            'question' => $this->createEnrolment($this->go1, $basicLiData + ['lo_id' => $this->electiveQuestionId, 'parent_lo_id' => $this->moduleId]),
            'text'     => $this->createEnrolment($this->go1, $basicLiData + ['lo_id' => $this->electiveTextId, 'parent_lo_id' => $this->moduleId]),
        ];
        $this->assertEquals(count($enrolments), EnrolmentHelper::countUserEnrolment($this->go1, $this->profileId));
        $this->assertEquals(count($enrolments), EnrolmentHelper::countUserEnrolment($this->go1, $this->profileId, $this->portalId));
        $this->assertEquals(0, EnrolmentHelper::countUserEnrolment($this->go1, 20202));
    }

    public function testLoadByLoAndProfileId()
    {
        $enrolmentId = $this->createEnrolment($this->go1, ['profile_id' => 1, 'taken_instance_id' => 1, 'lo_id' => 1]);
        $this->assertEquals($enrolmentId, EnrolmentHelper::loadByLoAndProfileId($this->go1, 1, 1)->id);

        $this->createEnrolment($this->go1, ['profile_id' => 1, 'taken_instance_id' => 2, 'lo_id' => 1]);
        $this->expectException(\Exception::class);
        EnrolmentHelper::loadByLoAndProfileId($this->go1, 1, 1);
    }

    public function testLoadByLoProfileAndPortal()
    {
        $fooEnrolmentId = $this->createEnrolment($this->go1, ['profile_id' => 1, 'taken_instance_id' => 1, 'lo_id' => 1]);
        $barEnrolmentId = $this->createEnrolment($this->go1, ['profile_id' => 1, 'taken_instance_id' => 2, 'lo_id' => 1]);

        $this->assertEquals($fooEnrolmentId, EnrolmentHelper::loadByLoProfileAndPortal($this->go1, 1, 1, 1)->id);
        $this->assertEquals($barEnrolmentId, EnrolmentHelper::loadByLoProfileAndPortal($this->go1, 1, 1, 2)->id);
    }

    public function testAssessors()
    {
        $enrolmentId = $this->createEnrolment($this->go1, ['lo_id' => 1, 'profile_id' => 1]);
        $assessor1Id = $this->createUser($this->go1, ['mail' => 'assessor1@mail.com']);
        $assessor2Id = $this->createUser($this->go1, ['mail' => 'assessor2@mail.com']);
        $this->createUser($this->go1, ['mail' => 'assessor3@mail.com']);

        $this->link($this->go1, EdgeTypes::HAS_TUTOR_ENROLMENT_EDGE, $assessor1Id, $enrolmentId);
        $this->link($this->go1, EdgeTypes::HAS_TUTOR_ENROLMENT_EDGE, $assessor2Id, $enrolmentId);

        $assessors = EnrolmentHelper::assessors($this->go1, $enrolmentId);

        $this->assertEquals(2, count($assessors));
        $this->assertEquals($assessor1Id, $assessors[0]->id);
        $this->assertEquals($assessor2Id, $assessors[1]->id);
    }

    public function testDueDate()
    {
        $enrolmentId = $this->createEnrolment($this->go1, ['lo_id' => 1, 'profile_id' => 1]);
        $this->assertNull(EnrolmentHelper::dueDate($this->go1, $enrolmentId));

        # Plan does not have due date
        $planId = $this->createPlan($this->go1, []);
        $this->link($this->go1, EdgeTypes::HAS_PLAN, $enrolmentId, $planId);
        $this->assertNull(EnrolmentHelper::dueDate($this->go1, $enrolmentId));

        # Plan does have due date
        $planId = $this->createPlan($this->go1, ['due_date' => '4 days']);
        $this->link($this->go1, EdgeTypes::HAS_PLAN, $enrolmentId, $planId);
        $this->assertTrue(EnrolmentHelper::dueDate($this->go1, $enrolmentId)->getTimestamp() > 0);

        # Enrolment has multiple plans
        $planId = $this->createPlan($this->go1, ['due_date' => '5 days', 'type' => PlanTypes::SUGGESTED]);
        $plan = PlanHelper::load($this->go1, $planId);
        $this->link($this->go1, EdgeTypes::HAS_PLAN, $enrolmentId, $planId);
        $this->assertTrue(EnrolmentHelper::dueDate($this->go1, $enrolmentId)->getTimestamp() > 0);
        $this->assertEquals(EnrolmentHelper::dueDate($this->go1, $enrolmentId), DateTime::create($plan->due_date));
    }

    public function testLoadUserEnrolment()
    {
        $enrolmentId = $this->createEnrolment($this->go1, [
            'lo_id'               => $loId = 2,
            'profile_id'          => $profileId = 3,
            'parent_enrolment_id' => $parentEnrolmentId = 5,
            'taken_instance_id'   => $takenPortalId = 5,
        ]);

        $enrolment = EnrolmentHelper::loadUserEnrolment($this->go1, $takenPortalId, $profileId, $loId, $parentEnrolmentId);
        $this->assertEquals($enrolmentId, $enrolment->id);
        $this->assertEquals($takenPortalId, $enrolment->takenPortalId);
        $this->assertNull(EnrolmentHelper::loadUserEnrolment($this->go1, 0, $profileId, $loId, $parentEnrolmentId));
    }

    public function testLoadSingleEnrolment()
    {
        $enrolmentId = $this->createEnrolment($this->go1, [
            'lo_id'               => $loId = 2,
            'profile_id'          => $profileId = 3,
            'parent_enrolment_id' => $parentEnrolmentId = 5,
            'taken_instance_id'   => $takenPortalId = 5,
        ]);

        $enrolment = EnrolmentHelper::loadSingle($this->go1, $enrolmentId);
        $this->assertEquals($enrolmentId, $enrolment->id);
        $this->assertEquals($takenPortalId, $enrolment->takenPortalId);
        $this->assertNull(EnrolmentHelper::loadSingle($this->go1, 0));
    }

    public function testParentEnrolment()
    {
        $data = ['profile_id' => $this->profileId, 'taken_instance_id' => $this->portalId];
        $courseEnrolmentId = $this->createEnrolment($this->go1, $data + ['lo_id' => $this->courseId]);
        $moduleEnrolmentId = $this->createEnrolment($this->go1, $data + ['lo_id' => $this->moduleId, 'parent_lo_id' => $this->courseId, 'parent_enrolment_id' => $courseEnrolmentId]);
        $videoEnrolmentId = $this->createEnrolment($this->go1, $data + ['lo_id' => $this->liVideoId, 'parent_lo_id' => $this->moduleId, 'parent_enrolment_id' => $moduleEnrolmentId]);

        $videoEnrolment = EnrolmentHelper::loadSingle($this->go1, $videoEnrolmentId);
        $moduleEnrolment = EnrolmentHelper::loadSingle($this->go1, $moduleEnrolmentId);

        $this->assertEquals($courseEnrolmentId, EnrolmentHelper::parentEnrolment($this->go1, $videoEnrolment)->id);
        $this->assertEquals($courseEnrolmentId, EnrolmentHelper::parentEnrolment($this->go1, $moduleEnrolment)->id);
        $this->assertEquals($moduleEnrolmentId, EnrolmentHelper::parentEnrolment($this->go1, $videoEnrolment, LoTypes::MODULE)->id);
    }
}
