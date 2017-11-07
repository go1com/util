<?php

namespace go1\util\tests\lo;

use DateTime;
use go1\util\edge\EdgeHelper;
use go1\util\edge\EdgeTypes;
use go1\util\lo\LiTypes;
use go1\util\lo\LoHelper;
use go1\util\lo\LoStatuses;
use go1\util\schema\mock\EnrolmentMockTrait;
use go1\util\schema\mock\InstanceMockTrait;
use go1\util\schema\mock\LoMockTrait;
use go1\util\schema\mock\UserMockTrait;
use go1\util\tests\UtilTestCase;
use HTMLPurifier;

class LoHelperTest extends UtilTestCase
{
    use UserMockTrait;
    use LoMockTrait;
    use InstanceMockTrait;
    use EnrolmentMockTrait;

    private $author1Id;
    private $author2Id;
    private $author3Id;
    private $author4Id;

    private $assessor1Id;
    private $assessor2Id;
    private $assessor3Id;
    private $course1Id;
    private $course2Id;
    private $module1Id;
    private $module2Id;
    private $resource1Id;
    private $resource2Id;

    public function setUp()
    {
        parent::setUp();
        $this->author1Id = $this->createUser($this->db, ['mail' => 'a1@mail.com']);
        $this->author2Id = $this->createUser($this->db, ['mail' => 'a2@mail.com']);
        $this->author3Id = $this->createUser($this->db, ['mail' => 'a3@mail.com']);
        $this->author4Id = $this->createUser($this->db, ['mail' => 'a4@mail.com']);
        $this->assessor1Id = $this->createUser($this->db, ['mail' => 'assessor1@mail.com']);
        $this->assessor2Id = $this->createUser($this->db, ['mail' => 'assessor2@mail.com']);
        $this->assessor3Id = $this->createUser($this->db, ['mail' => 'assessor3@mail.com']);

        $this->course1Id = $this->createCourse($this->db, ['instance_id' => $this->createInstance($this->db, [])]);
        $this->course2Id = $this->createCourse($this->db, ['instance_id' => $this->createInstance($this->db, [])]);

        $this->module1Id = $this->createModule($this->db);
        $this->module2Id = $this->createModule($this->db);

        $this->resource1Id = $this->createLO($this->db, ['type' => LiTypes::RESOURCE]);
        $this->resource2Id = $this->createLO($this->db, ['type' => LiTypes::RESOURCE]);

        $this->link($this->db, EdgeTypes::COURSE_ASSESSOR, $this->course1Id, $this->assessor1Id);
        $this->link($this->db, EdgeTypes::COURSE_ASSESSOR, $this->course1Id, $this->assessor2Id);
        $this->link($this->db, EdgeTypes::COURSE_ASSESSOR, $this->module1Id, $this->assessor2Id);
        $this->link($this->db, EdgeTypes::COURSE_ASSESSOR, $this->resource1Id, $this->assessor3Id);
        $this->link($this->db, EdgeTypes::COURSE_ASSESSOR, $this->resource2Id, $this->assessor2Id);

        $this->link($this->db, EdgeTypes::HAS_MODULE, $this->course1Id, $this->module1Id);
        $this->link($this->db, EdgeTypes::HAS_MODULE, $this->course2Id, $this->module2Id);
        $this->link($this->db, EdgeTypes::HAS_LI, $this->module1Id, $this->resource1Id);
        $this->link($this->db, EdgeTypes::HAS_LI, $this->module2Id, $this->resource1Id);
        $this->link($this->db, EdgeTypes::HAS_LI, $this->module2Id, $this->resource2Id);
        $this->link($this->db, EdgeTypes::HAS_AUTHOR_EDGE, $this->course1Id, $this->author1Id);
        $this->link($this->db, EdgeTypes::HAS_AUTHOR_EDGE, $this->course1Id, $this->author2Id);
        $this->link($this->db, EdgeTypes::HAS_AUTHOR_EDGE, $this->module1Id, $this->author1Id);
        $this->link($this->db, EdgeTypes::HAS_AUTHOR_EDGE, $this->module1Id, $this->author2Id);
        $this->link($this->db, EdgeTypes::HAS_AUTHOR_EDGE, $this->resource1Id, $this->author3Id);
        $this->link($this->db, EdgeTypes::HAS_AUTHOR_EDGE, $this->module2Id, $this->author4Id);
    }

    public function testAssessor()
    {
        $assessors = LoHelper::assessorIds($this->db, $this->course1Id);
        $this->assertEquals(2, count($assessors));
        $this->assertEquals($this->assessor1Id, $assessors[0]);
        $this->assertEquals($this->assessor2Id, $assessors[1]);
    }

    public function testHasActiveMembership()
    {
        $this->db->insert('gc_lo_group', ['lo_id' => $loId = 10, 'instance_id' => 20]);
        $this->db->insert('gc_lo_group', ['lo_id' => $loId, 'instance_id' => 30]);
        $this->assertTrue(LoHelper::hasActiveMembership($this->db, $loId, 20));
        $this->assertTrue(LoHelper::hasActiveMembership($this->db, $loId, 30));
        $this->assertFalse(LoHelper::hasActiveMembership($this->db, $loId, 40));
    }

    public function testDescriptionPurifierConfig()
    {
        $html = new HTMLPurifier();
        $data = [
            'Plain text'                                           => 'Plain text',
            'foo <span style="color:#0000aa;">data</span>'         => 'foo <span style="color:#0000aa;">data</span>',
            '<a href="test.html" target="_blank">Invalid link</a>' => '<a href="test.html" target="_blank" rel="noreferrer noopener">Invalid link</a>',
        ];
        foreach ($data as $input => $expect) {
            $result = $html->purify(trim($input), LoHelper::descriptionPurifierConfig());
            $this->assertEquals($expect, $result);
        }
    }

    public function testLoadEvent()
    {
        $courseId = $this->createCourse($this->db, ['event' => [
            'start' => $start = (new DateTime('+1 day'))->format(DATE_ISO8601),
            'end'   => $end = (new DateTime('+2 days'))->format(DATE_ISO8601),
        ]]);
        $lo = LoHelper::load($this->db, $courseId);
        $this->assertNotEmpty($lo->event);
        $this->assertEquals($start, $lo->event->start);
        $this->assertEquals($end, $lo->event->end);
    }

    public function testLoadNoEvent()
    {
        $courseId = $this->createCourse($this->db);
        $lo = LoHelper::load($this->db, $courseId);
        $this->assertEquals((object) [], $lo->event);
    }

    public function testLoadCustomTag()
    {
        $instanceId = 999;
        $courseId = $this->createCourse($this->db, ['instance_id' => $instanceId]);
        $this->db->insert('gc_lo_tag', ['instance_id' => $instanceId, 'lo_id' => $courseId, 'tag' => 'foo', 'status' => 1]);
        $this->db->insert('gc_lo_tag', ['instance_id' => $instanceId, 'lo_id' => $courseId, 'tag' => 'bar', 'status' => 1]);
        $course = LoHelper::load($this->db, $courseId, $instanceId);
        $this->assertEquals(['bar', 'foo'], $course->custom_tags);
    }

    public function testParentsAuthorIds()
    {
        # Course 1
        $authors = LoHelper::parentsAuthorIds($this->db, $this->course1Id);
        $this->assertEquals(2, count($authors));
        $this
            ->hasAuthor($this->author1Id, $authors)
            ->hasAuthor($this->author2Id, $authors);

        # Course 2
        $authors = LoHelper::parentsAuthorIds($this->db, $this->course2Id);
        $this->assertEquals(0, count($authors));

        # Module 1
        $authors = LoHelper::parentsAuthorIds($this->db, $this->module1Id);
        $this->assertEquals(2, count($authors));
        $this
            ->hasAuthor($this->author1Id, $authors)
            ->hasAuthor($this->author2Id, $authors);

        # Module 2
        $authors = LoHelper::parentsAuthorIds($this->db, $this->module2Id);
        $this->assertEquals(1, count($authors));
        $this
        ->hasAuthor($this->author4Id, $authors);

        # Resource 1
        $authors = LoHelper::parentsAuthorIds($this->db, $this->resource1Id);
        $this->assertEquals(4, count($authors));
        $this
            ->hasAuthor($this->author1Id, $authors)
            ->hasAuthor($this->author2Id, $authors)
            ->hasAuthor($this->author3Id, $authors)
            ->hasAuthor($this->author4Id, $authors);

        # Resource 2
        $authors = LoHelper::parentsAuthorIds($this->db, $this->resource2Id);
        $this->assertEquals(1, count($authors));
        $this->hasAuthor($this->author4Id, $authors);
    }

    public function testParentAssessorIds() {
        $assessors = LoHelper::parentsAssessorIds($this->db, $this->course1Id);
        $this->assertEquals(2, count($assessors));
        $this
            ->hasAssessor($this->assessor1Id, $assessors)
            ->hasAssessor($this->assessor2Id, $assessors);

        $assessors = LoHelper::parentsAssessorIds($this->db, $this->course2Id);
        $this->assertEquals(0, count($assessors));

        $assessors = LoHelper::parentsAssessorIds($this->db, $this->module1Id);
        $this->assertEquals(2, count($assessors));
        $this
            ->hasAssessor($this->assessor1Id, $assessors)
            ->hasAssessor($this->assessor2Id, $assessors);

        $assessors = LoHelper::parentsAssessorIds($this->db, $this->resource1Id);
        $this->assertEquals(3, count($assessors));
        $this
            ->hasAssessor($this->assessor1Id, $assessors)
            ->hasAssessor($this->assessor2Id, $assessors)
            ->hasAssessor($this->assessor3Id, $assessors);

        $assessors = LoHelper::parentsAssessorIds($this->db, $this->module2Id);
        $this->assertEquals(0, count($assessors));

        $assessors = LoHelper::parentsAssessorIds($this->db, $this->resource2Id);
        $this->assertEquals(1, count($assessors));
        $this
            ->hasAssessor($this->assessor2Id, $assessors);
    }

    public function testParentAssessorIdsIncludeEnrolmentAssessors() {
        $course1EnrolmentId = $this->createEnrolment($this->db, ['lo_id' => $this->course1Id, 'profile_id' => $learnerProfileId = 123]);
        $module1EnrolmentId = $this->createEnrolment($this->db, ['lo_id' => $this->module1Id, 'profile_id' => $learnerProfileId]);
        $li1EnrolmentId = $this->createEnrolment($this->db, ['lo_id' => $this->resource1Id, 'profile_id' => $learnerProfileId]);
        $li2EnrolmentId = $this->createEnrolment($this->db, ['lo_id' => $this->resource2Id, 'profile_id' => $learnerProfileId]);

        $this->link($this->db, EdgeTypes::HAS_TUTOR_ENROLMENT_EDGE, $this->author1Id, $course1EnrolmentId);
        $this->link($this->db, EdgeTypes::HAS_TUTOR_ENROLMENT_EDGE, $this->author2Id, $li1EnrolmentId);
        $this->link($this->db, EdgeTypes::HAS_TUTOR_ENROLMENT_EDGE, $this->author3Id, $li2EnrolmentId);

        $assessors = LoHelper::parentsAssessorIds($this->db, $this->course1Id, null, $learnerProfileId);
        $this->assertEquals(3, count($assessors));
        $this
            ->hasAssessor($this->assessor1Id, $assessors)
            ->hasAssessor($this->assessor2Id, $assessors)
            ->hasAssessor($this->author1Id, $assessors);

        $assessors = LoHelper::parentsAssessorIds($this->db, $this->course2Id, null, $learnerProfileId);
        $this->assertEquals(0, count($assessors));

        $assessors = LoHelper::parentsAssessorIds($this->db, $this->module1Id, null, $learnerProfileId);
        $this->assertEquals(3, count($assessors));
        $this
            ->hasAssessor($this->assessor1Id, $assessors)
            ->hasAssessor($this->assessor2Id, $assessors)
            ->hasAssessor($this->author1Id, $assessors);

        $assessors = LoHelper::parentsAssessorIds($this->db, $this->resource1Id, null, $learnerProfileId);
        $this->assertEquals(5, count($assessors));
        $this
            ->hasAssessor($this->assessor1Id, $assessors)
            ->hasAssessor($this->assessor2Id, $assessors)
            ->hasAssessor($this->assessor3Id, $assessors)
            ->hasAssessor($this->author1Id, $assessors)
            ->hasAssessor($this->author2Id, $assessors);

        $assessors = LoHelper::parentsAssessorIds($this->db, $this->module2Id, null, $learnerProfileId);
        $this->assertEquals(0, count($assessors));

        $assessors = LoHelper::parentsAssessorIds($this->db, $this->resource2Id, null, $learnerProfileId);
        $this->assertEquals(2, count($assessors));
        $this
            ->hasAssessor($this->assessor2Id, $assessors)
            ->hasAssessor($this->author3Id, $assessors);
    }

    public function testParentIds()
    {
        # Course 1
        $parentIds = LoHelper::parentIds($this->db, $this->course1Id);
        $this->assertEquals(0, count($parentIds));

        # Course 2
        $parentIds = LoHelper::parentIds($this->db, $this->course2Id);
        $this->assertEquals(0, count($parentIds));

        # Module 1
        $parentIds = LoHelper::parentIds($this->db, $this->module1Id);
        $this->assertEquals(1, count($parentIds));
        $this->hasParent($this->course1Id, $parentIds);

        # Module 2
        $parentIds = LoHelper::parentIds($this->db, $this->module2Id);
        $this->assertEquals(1, count($parentIds));
        $this->hasParent($this->course2Id, $parentIds);

        # Resource 1
        $parentIds = LoHelper::parentIds($this->db, $this->resource1Id);
        $this->assertEquals(4, count($parentIds));
        $this
            ->hasParent($this->module1Id, $parentIds)
            ->hasParent($this->module2Id, $parentIds)
            ->hasParent($this->course1Id, $parentIds)
            ->hasParent($this->course2Id, $parentIds);

        # Resource 2
        $parentIds = LoHelper::parentIds($this->db, $this->resource2Id);
        $this->assertEquals(2, count($parentIds));
        $this
            ->hasParent($this->module2Id, $parentIds)
            ->hasParent($this->course2Id, $parentIds);
    }

    public function testChildIds()
    {
        # Course 1
        $childIds = LoHelper::childIds($this->db, $this->course1Id);
        $this->assertEquals(1, count($childIds));
        $this->hasChild($this->module1Id, $childIds);

        # Course 1 All child
        $childIds = LoHelper::childIds($this->db, $this->course1Id, true);
        $this->assertEquals(2, count($childIds));
        $this->hasChild($this->module1Id, $childIds);
        $this->hasChild($this->resource1Id, $childIds);

        # Course 2
        $childIds = LoHelper::childIds($this->db, $this->course2Id);
        $this->assertEquals(1, count($childIds));
        $this->hasChild($this->module2Id, $childIds);

        # Module 1
        $childIds = LoHelper::childIds($this->db, $this->module1Id);
        $this->assertEquals(1, count($childIds));
        $this->hasChild($this->resource1Id, $childIds);

        # Module 2
        $childIds = LoHelper::childIds($this->db, $this->module2Id);
        $this->assertEquals(2, count($childIds));
        $this
            ->hasChild($this->resource1Id, $childIds)
            ->hasChild($this->resource2Id, $childIds);

        # Resource 1
        $childIds = LoHelper::childIds($this->db, $this->resource1Id);
        $this->assertEquals(0, count($childIds));

        # Resource 2
        $childIds = LoHelper::childIds($this->db, $this->resource2Id);
        $this->assertEquals(0, count($childIds));
    }

    public function testCountEnrolment()
    {
        $this->createEnrolment($this->db, ['profile_id' => 1, 'lo_id' => $this->course1Id]);
        $this->createEnrolment($this->db, ['profile_id' => 2, 'lo_id' => $this->course1Id]);
        $this->createEnrolment($this->db, ['profile_id' => 3, 'lo_id' => $this->course1Id]);
        $this->assertEquals(3, LoHelper::countEnrolment($this->db, $this->course1Id));
    }

    public function testGetCustomisation()
    {
        $courseId = 123;
        $instanceId = 555;
        $this->link($this->db,EdgeTypes::HAS_LO_CUSTOMISATION, $courseId, $instanceId, 0, [
            'tokens' => $tokens = [
                'token_1' => 'value 1',
                'token_2' => 'value 2',
            ],
            'published' => LoStatuses::ARCHIVED
        ]);

        $customize = LoHelper::getCustomisation($this->db, $courseId, $instanceId);
        $this->assertEquals($customize['published'], LoStatuses::ARCHIVED);
        $this->assertEquals($customize['tokens']['token_1'], $tokens['token_1']);
    }

    public function testIsSingleLi()
    {
        $videoId = $this->createVideo($this->db, ['instance_id' => $this->createInstance($this->db, []), 'data' => [LoHelper::SINGLE_LI => true]]);
        $video = LoHelper::load($this->db, $videoId);
        $this->assertTrue(LoHelper::isSingleLi($video));
        $this->assertFalse(LoHelper::isSingleLi(LoHelper::load($this->db, $this->course1Id)));
    }

    private function hasAuthor($authorId, array $source)
    {
        $this->assertTrue(in_array($authorId, $source));

        return $this;
    }

    private function hasAssessor($assessorId, array $source)
    {
        $this->assertTrue(in_array($assessorId, $source));

        return $this;
    }

    private function hasParent($parentId, array $source)
    {
        $this->assertTrue(in_array($parentId, $source));

        return $this;
    }

    private function hasChild($childId, array $source)
    {
        $this->assertTrue(in_array($childId, $source));

        return $this;
    }
}
