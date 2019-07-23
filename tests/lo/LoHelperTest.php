<?php

namespace go1\util\tests\lo;

use DateTime;
use go1\util\edge\EdgeTypes;
use go1\util\lo\LiTypes;
use go1\util\lo\LoHelper;
use go1\util\lo\LoAttributes;
use go1\util\lo\LoStatuses;
use go1\util\lo\LoSuggestedCompletionTypes;
use go1\util\schema\mock\EnrolmentMockTrait;
use go1\util\schema\mock\LoMockTrait;
use go1\util\schema\mock\PortalMockTrait;
use go1\util\schema\mock\UserMockTrait;
use go1\util\tests\UtilCoreTestCase;
use HTMLPurifier;

class LoHelperTest extends UtilCoreTestCase
{
    use UserMockTrait;
    use LoMockTrait;
    use PortalMockTrait;
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

    public function setUp() : void
    {
        parent::setUp();

        $this->author1Id = $this->createUser($this->go1, ['mail' => 'a1@mail.com']);
        $this->author2Id = $this->createUser($this->go1, ['mail' => 'a2@mail.com']);
        $this->author3Id = $this->createUser($this->go1, ['mail' => 'a3@mail.com']);
        $this->author4Id = $this->createUser($this->go1, ['mail' => 'a4@mail.com']);
        $this->assessor1Id = $this->createUser($this->go1, ['mail' => 'assessor1@mail.com']);
        $this->assessor2Id = $this->createUser($this->go1, ['mail' => 'assessor2@mail.com']);
        $this->assessor3Id = $this->createUser($this->go1, ['mail' => 'assessor3@mail.com']);

        $this->course1Id = $this->createCourse($this->go1, ['instance_id' => $this->createPortal($this->go1, [])]);
        $this->course2Id = $this->createCourse($this->go1, ['instance_id' => $this->createPortal($this->go1, [])]);

        $this->module1Id = $this->createModule($this->go1);
        $this->module2Id = $this->createModule($this->go1);

        $this->resource1Id = $this->createLO($this->go1, ['type' => LiTypes::RESOURCE]);
        $this->resource2Id = $this->createLO($this->go1, ['type' => LiTypes::RESOURCE]);

        $this->link($this->go1, EdgeTypes::COURSE_ASSESSOR, $this->course1Id, $this->assessor1Id);
        $this->link($this->go1, EdgeTypes::COURSE_ASSESSOR, $this->course1Id, $this->assessor2Id);
        $this->link($this->go1, EdgeTypes::COURSE_ASSESSOR, $this->module1Id, $this->assessor2Id);
        $this->link($this->go1, EdgeTypes::COURSE_ASSESSOR, $this->resource1Id, $this->assessor3Id);
        $this->link($this->go1, EdgeTypes::COURSE_ASSESSOR, $this->resource2Id, $this->assessor2Id);

        $this->link($this->go1, EdgeTypes::HAS_MODULE, $this->course1Id, $this->module1Id);
        $this->link($this->go1, EdgeTypes::HAS_MODULE, $this->course2Id, $this->module2Id);
        $this->link($this->go1, EdgeTypes::HAS_LI, $this->module1Id, $this->resource1Id);
        $this->link($this->go1, EdgeTypes::HAS_LI, $this->module2Id, $this->resource1Id);
        $this->link($this->go1, EdgeTypes::HAS_LI, $this->module2Id, $this->resource2Id);
        $this->link($this->go1, EdgeTypes::HAS_AUTHOR_EDGE, $this->course1Id, $this->author1Id);
        $this->link($this->go1, EdgeTypes::HAS_AUTHOR_EDGE, $this->course1Id, $this->author2Id);
        $this->link($this->go1, EdgeTypes::HAS_AUTHOR_EDGE, $this->module1Id, $this->author1Id);
        $this->link($this->go1, EdgeTypes::HAS_AUTHOR_EDGE, $this->module1Id, $this->author2Id);
        $this->link($this->go1, EdgeTypes::HAS_AUTHOR_EDGE, $this->resource1Id, $this->author3Id);
        $this->link($this->go1, EdgeTypes::HAS_AUTHOR_EDGE, $this->module2Id, $this->author4Id);
    }

    public function testAssessor()
    {
        $assessors = LoHelper::assessorIds($this->go1, $this->course1Id);
        $this->assertEquals(2, count($assessors));
        $this->assertEquals($this->assessor1Id, $assessors[0]);
        $this->assertEquals($this->assessor2Id, $assessors[1]);
    }

    public function testHasActiveMembership()
    {
        $this->go1->insert('gc_lo_group', ['lo_id' => $loId = 10, 'instance_id' => 20]);
        $this->go1->insert('gc_lo_group', ['lo_id' => $loId, 'instance_id' => 30]);
        $this->assertTrue(LoHelper::hasActiveMembership($this->go1, $loId, 20));
        $this->assertTrue(LoHelper::hasActiveMembership($this->go1, $loId, 30));
        $this->assertFalse(LoHelper::hasActiveMembership($this->go1, $loId, 40));
    }

    public function dataDescriptionPurifierConfig()
    {
        return [
            ['Plain text', 'Plain text'],
            ['foo <span style="color:#0000aa;">data</span>', 'foo <span style="color:#0000aa;">data</span>'],
            ['<a href="test.html" target="_blank">Invalid link</a>', '<a href="test.html" target="_blank" rel="noreferrer noopener">Invalid link</a>'],
            ['<iframe width="560" height="315" src="https://www.youtube.com/embed/xxx" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>', '<iframe width="560" height="315" src="https://www.youtube.com/embed/xxx" frameborder="0" allowfullscreen=""></iframe>'],
            ['<iframe src="https://player.vimeo.com/video/xxx" width="640" height="360" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>', '<iframe src="https://player.vimeo.com/video/xxx" width="640" height="360" frameborder="0" webkitallowfullscreen="" mozallowfullscreen="" allowfullscreen=""></iframe>'],
            ['<iframe src="https://fast.wistia.net/embed/iframe/xxx?seo=false&videoFoam=true" title="Wistia video player" allowtransparency="true" frameborder="0" scrolling="no" class="wistia_embed" name="wistia_embed" allowfullscreen mozallowfullscreen webkitallowfullscreen oallowfullscreen msallowfullscreen width="100%" height="100%"></iframe>', '<iframe src="https://fast.wistia.net/embed/iframe/xxx?seo=false&amp;videoFoam=true" title="Wistia video player" frameborder="0" class="wistia_embed" allowfullscreen="" mozallowfullscreen="" webkitallowfullscreen="" width="100%" height="100%"></iframe>'],
            ['<iframe src="https://www.w3schools.com"></iframe>', '<iframe></iframe>'],
        ];
    }

    /** @dataProvider dataDescriptionPurifierConfig */
    public function testDescriptionPurifierConfig(string $input, string $expect)
    {
        $html = new HTMLPurifier;
        $result = $html->purify(trim($input), LoHelper::descriptionPurifierConfig());
        $this->assertEquals($expect, $result);
    }

    public function testLoadEvent()
    {
        $courseId = $this->createCourse($this->go1, ['event' => [
            'start' => $start = (new DateTime('+1 day'))->format(DATE_ISO8601),
            'end'   => $end = (new DateTime('+2 days'))->format(DATE_ISO8601),
        ]]);
        $lo = LoHelper::load($this->go1, $courseId);
        $this->assertNotEmpty($lo->event);
        $this->assertEquals($start, $lo->event->start);
        $this->assertEquals($end, $lo->event->end);
    }

    public function testLoadNoEvent()
    {
        $courseId = $this->createCourse($this->go1);
        $lo = LoHelper::load($this->go1, $courseId);
        $this->assertEquals((object) [], $lo->event);
    }

    public function testLoadCustomTag()
    {
        $instanceId = 999;
        $courseId = $this->createCourse($this->go1, ['instance_id' => $instanceId]);
        $this->go1->insert('gc_lo_tag', ['instance_id' => $instanceId, 'lo_id' => $courseId, 'tag' => 'foo', 'status' => 1]);
        $this->go1->insert('gc_lo_tag', ['instance_id' => $instanceId, 'lo_id' => $courseId, 'tag' => 'bar', 'status' => 1]);
        $course = LoHelper::load($this->go1, $courseId, $instanceId);
        $this->assertEquals(['bar', 'foo'], $course->custom_tags);
    }

    public function testParentsAuthorIds()
    {
        # Course 1
        $authors = LoHelper::parentsAuthorIds($this->go1, $this->course1Id);
        $this->assertEquals(2, count($authors));
        $this
            ->hasAuthor($this->author1Id, $authors)
            ->hasAuthor($this->author2Id, $authors);

        # Course 2
        $authors = LoHelper::parentsAuthorIds($this->go1, $this->course2Id);
        $this->assertEquals(0, count($authors));

        # Module 1
        $authors = LoHelper::parentsAuthorIds($this->go1, $this->module1Id);
        $this->assertEquals(2, count($authors));
        $this
            ->hasAuthor($this->author1Id, $authors)
            ->hasAuthor($this->author2Id, $authors);

        # Module 2
        $authors = LoHelper::parentsAuthorIds($this->go1, $this->module2Id);
        $this->assertEquals(1, count($authors));
        $this
            ->hasAuthor($this->author4Id, $authors);

        # Resource 1
        $authors = LoHelper::parentsAuthorIds($this->go1, $this->resource1Id);
        $this->assertEquals(4, count($authors));
        $this
            ->hasAuthor($this->author1Id, $authors)
            ->hasAuthor($this->author2Id, $authors)
            ->hasAuthor($this->author3Id, $authors)
            ->hasAuthor($this->author4Id, $authors);

        # Resource 2
        $authors = LoHelper::parentsAuthorIds($this->go1, $this->resource2Id);
        $this->assertEquals(1, count($authors));
        $this->hasAuthor($this->author4Id, $authors);
    }

    public function testParentAssessorIds()
    {
        $assessors = LoHelper::parentsAssessorIds($this->go1, $this->course1Id);
        $this->assertEquals(2, count($assessors));
        $this
            ->hasAssessor($this->assessor1Id, $assessors)
            ->hasAssessor($this->assessor2Id, $assessors);

        $assessors = LoHelper::parentsAssessorIds($this->go1, $this->course2Id);
        $this->assertEquals(0, count($assessors));

        $assessors = LoHelper::parentsAssessorIds($this->go1, $this->module1Id);
        $this->assertEquals(2, count($assessors));
        $this
            ->hasAssessor($this->assessor1Id, $assessors)
            ->hasAssessor($this->assessor2Id, $assessors);

        $assessors = LoHelper::parentsAssessorIds($this->go1, $this->resource1Id);
        $this->assertEquals(3, count($assessors));
        $this
            ->hasAssessor($this->assessor1Id, $assessors)
            ->hasAssessor($this->assessor2Id, $assessors)
            ->hasAssessor($this->assessor3Id, $assessors);

        $assessors = LoHelper::parentsAssessorIds($this->go1, $this->module2Id);
        $this->assertEquals(0, count($assessors));

        $assessors = LoHelper::parentsAssessorIds($this->go1, $this->resource2Id);
        $this->assertEquals(1, count($assessors));
        $this
            ->hasAssessor($this->assessor2Id, $assessors);
    }

    public function testParentAssessorIdsIncludeEnrolmentAssessors()
    {
        $course1EnrolmentId = $this->createEnrolment($this->go1, ['lo_id' => $this->course1Id, 'profile_id' => $learnerProfileId = 123]);
        $module1EnrolmentId = $this->createEnrolment($this->go1, ['lo_id' => $this->module1Id, 'profile_id' => $learnerProfileId]);
        $li1EnrolmentId = $this->createEnrolment($this->go1, ['lo_id' => $this->resource1Id, 'profile_id' => $learnerProfileId]);
        $li2EnrolmentId = $this->createEnrolment($this->go1, ['lo_id' => $this->resource2Id, 'profile_id' => $learnerProfileId]);

        $this->link($this->go1, EdgeTypes::HAS_TUTOR_ENROLMENT_EDGE, $this->author1Id, $course1EnrolmentId);
        $this->link($this->go1, EdgeTypes::HAS_TUTOR_ENROLMENT_EDGE, $this->author2Id, $li1EnrolmentId);
        $this->link($this->go1, EdgeTypes::HAS_TUTOR_ENROLMENT_EDGE, $this->author3Id, $li2EnrolmentId);

        $assessors = LoHelper::parentsAssessorIds($this->go1, $this->course1Id, null, $learnerProfileId);
        $this->assertEquals(3, count($assessors));
        $this
            ->hasAssessor($this->assessor1Id, $assessors)
            ->hasAssessor($this->assessor2Id, $assessors)
            ->hasAssessor($this->author1Id, $assessors);

        $assessors = LoHelper::parentsAssessorIds($this->go1, $this->course2Id, null, $learnerProfileId);
        $this->assertEquals(0, count($assessors));

        $assessors = LoHelper::parentsAssessorIds($this->go1, $this->module1Id, null, $learnerProfileId);
        $this->assertEquals(3, count($assessors));
        $this
            ->hasAssessor($this->assessor1Id, $assessors)
            ->hasAssessor($this->assessor2Id, $assessors)
            ->hasAssessor($this->author1Id, $assessors);

        $assessors = LoHelper::parentsAssessorIds($this->go1, $this->resource1Id, null, $learnerProfileId);
        $this->assertEquals(5, count($assessors));
        $this
            ->hasAssessor($this->assessor1Id, $assessors)
            ->hasAssessor($this->assessor2Id, $assessors)
            ->hasAssessor($this->assessor3Id, $assessors)
            ->hasAssessor($this->author1Id, $assessors)
            ->hasAssessor($this->author2Id, $assessors);

        $assessors = LoHelper::parentsAssessorIds($this->go1, $this->module2Id, null, $learnerProfileId);
        $this->assertEquals(0, count($assessors));

        $assessors = LoHelper::parentsAssessorIds($this->go1, $this->resource2Id, null, $learnerProfileId);
        $this->assertEquals(2, count($assessors));
        $this
            ->hasAssessor($this->assessor2Id, $assessors)
            ->hasAssessor($this->author3Id, $assessors);
    }

    public function testParentIds()
    {
        # Course 1
        $parentIds = LoHelper::parentIds($this->go1, $this->course1Id);
        $this->assertEquals(0, count($parentIds));

        # Course 2
        $parentIds = LoHelper::parentIds($this->go1, $this->course2Id);
        $this->assertEquals(0, count($parentIds));

        # Module 1
        $parentIds = LoHelper::parentIds($this->go1, $this->module1Id);
        $this->assertEquals(1, count($parentIds));
        $this->hasParent($this->course1Id, $parentIds);

        # Module 2
        $parentIds = LoHelper::parentIds($this->go1, $this->module2Id);
        $this->assertEquals(1, count($parentIds));
        $this->hasParent($this->course2Id, $parentIds);

        # Resource 1
        $parentIds = LoHelper::parentIds($this->go1, $this->resource1Id);
        $this->assertEquals(4, count($parentIds));
        $this
            ->hasParent($this->module1Id, $parentIds)
            ->hasParent($this->module2Id, $parentIds)
            ->hasParent($this->course1Id, $parentIds)
            ->hasParent($this->course2Id, $parentIds);

        # Resource 1 without recursive
        $parentIds = LoHelper::parentIds($this->go1, $this->resource1Id, false);
        $this->assertEquals(2, count($parentIds));
        $this
            ->hasParent($this->module1Id, $parentIds)
            ->hasParent($this->module2Id, $parentIds);

        # Resource 2
        $parentIds = LoHelper::parentIds($this->go1, $this->resource2Id);
        $this->assertEquals(2, count($parentIds));
        $this
            ->hasParent($this->module2Id, $parentIds)
            ->hasParent($this->course2Id, $parentIds);
    }

    public function testLoadAttributes()
    {
        $courseId = $this->createCourse($this->go1, ['course' => []]);
        $this->go1->insert('gc_lo_attributes', [
            'id'        => null,
            'lo_id'     => $courseId,
            'key'       => LoAttributes::MOBILE_OPTIMISED,
            'value'     => 1,
            'created'   => 0
        ]);
        $lo = LoHelper::load($this->go1, $courseId, null, false, true);
        $this->assertNotEmpty($lo->attributes);
        $this->assertEquals($lo->attributes->{LoAttributes::machineName(LoAttributes::MOBILE_OPTIMISED)}, 1);
    }

    public function testLoadAttributesWithLookup()
    {
        $courseId = $this->createCourse($this->go1, ['course' => []]);
        $this->go1->insert('gc_lo_attributes', [
            'id'        => null,
            'lo_id'     => $courseId,
            'key'       => LoAttributes::REGION_RESTRICTIONS,
            'value'     => '["AU"]',
            'created'   => 0
        ]);
        $this->go1->insert('gc_lo_attributes_lookup', [
            'id'                => null,
            'name'              => LoAttributes::machineName(LoAttributes::REGION_RESTRICTIONS),
            'key'               => LoAttributes::REGION_RESTRICTIONS,
            'attribute_type'    => 'TEXT',
            'lo_type'           => 'course',
            'required'          => '["NO"]',
            'permission'        => '["Author","AccountsOnAdmin","Admin","None"]',
            'is_array'          => 1
        ]);
        $lo = LoHelper::load($this->go1, $courseId, null, false, true);
        $this->assertNotEmpty($lo->attributes);
        $this->assertObjectHasAttribute(LoAttributes::machineName(LoAttributes::REGION_RESTRICTIONS), $lo->attributes);
    }

    public function testChildIds()
    {
        # Course 1
        $childIds = LoHelper::childIds($this->go1, $this->course1Id);
        $this->assertEquals(1, count($childIds));
        $this->hasChild($this->module1Id, $childIds);

        # Course 1 All child
        $childIds = LoHelper::childIds($this->go1, $this->course1Id, true);
        $this->assertEquals(2, count($childIds));
        $this->hasChild($this->module1Id, $childIds);
        $this->hasChild($this->resource1Id, $childIds);

        # Course 2
        $childIds = LoHelper::childIds($this->go1, $this->course2Id);
        $this->assertEquals(1, count($childIds));
        $this->hasChild($this->module2Id, $childIds);

        # Module 1
        $childIds = LoHelper::childIds($this->go1, $this->module1Id);
        $this->assertEquals(1, count($childIds));
        $this->hasChild($this->resource1Id, $childIds);

        # Module 2
        $childIds = LoHelper::childIds($this->go1, $this->module2Id);
        $this->assertEquals(2, count($childIds));
        $this
            ->hasChild($this->resource1Id, $childIds)
            ->hasChild($this->resource2Id, $childIds);

        # Resource 1
        $childIds = LoHelper::childIds($this->go1, $this->resource1Id);
        $this->assertEquals(0, count($childIds));

        # Resource 2
        $childIds = LoHelper::childIds($this->go1, $this->resource2Id);
        $this->assertEquals(0, count($childIds));
    }

    public function testCountEnrolment()
    {
        $this->createEnrolment($this->go1, ['profile_id' => 1, 'lo_id' => $this->course1Id]);
        $this->createEnrolment($this->go1, ['profile_id' => 2, 'lo_id' => $this->course1Id]);
        $this->createEnrolment($this->go1, ['profile_id' => 3, 'lo_id' => $this->course1Id]);
        $this->assertEquals(3, LoHelper::countEnrolment($this->go1, $this->course1Id));
    }

    public function testGetCustomisation()
    {
        $courseId = 123;
        $instanceId = 555;
        $this->link($this->go1, EdgeTypes::HAS_LO_CUSTOMISATION, $courseId, $instanceId, 0, [
            'tokens'    => $tokens = [
                'token_1' => 'value 1',
                'token_2' => 'value 2',
            ],
            'published' => LoStatuses::ARCHIVED,
        ]);

        $customize = LoHelper::getCustomisation($this->go1, $courseId, $instanceId);
        $this->assertEquals($customize['published'], LoStatuses::ARCHIVED);
        $this->assertEquals($customize['tokens']['token_1'], $tokens['token_1']);
    }

    public function testIsSingleLi()
    {
        $videoId = $this->createVideo($this->go1, ['instance_id' => $this->createPortal($this->go1, []), 'data' => [LoHelper::SINGLE_LI => true]]);
        $video = LoHelper::load($this->go1, $videoId);
        $this->assertTrue(LoHelper::isSingleLi($video));
        $this->assertFalse(LoHelper::isSingleLi(LoHelper::load($this->go1, $this->course1Id)));
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

    public function testAuthorIds()
    {
        $authors = LoHelper::authorIds($this->go1, $this->course1Id);

        $this->assertEquals(2, count($authors));
        $this->assertEquals($this->author1Id, $authors[0]);
        $this->assertEquals($this->author2Id, $authors[1]);
    }

    public function testAuthors()
    {
        $authors = LoHelper::authors($this->go1, $this->course1Id);

        $this->assertEquals(2, count($authors));
        $this->assertEquals($this->author1Id, $authors[0]->id);
        $this->assertEquals($this->author2Id, $authors[1]->id);
    }

    public function testGetSuggestedCompletion()
    {
        $this->link($this->go1, EdgeTypes::HAS_SUGGESTED_COMPLETION, $this->course1Id, 0, 0, json_encode([
            'type'  => 2,
            'value' => '3 days',
        ]));

        list($type, $value) = LoHelper::getSuggestedCompletion($this->go1, $this->course1Id);
        $this->assertEquals($type, LoSuggestedCompletionTypes::E_DURATION);
        $this->assertEquals($value, '3 days');
    }

    public function testGetSuggestedCompletionWithSingleLI()
    {
        $videoId = $this->createVideo($this->go1, ['instance_id' => $this->createPortal($this->go1, []), 'data' => [LoHelper::SINGLE_LI => true]]);
        $targetId = $this->link($this->go1, EdgeTypes::HAS_LI, $this->module1Id, $videoId);
        $this->link($this->go1, EdgeTypes::HAS_SUGGESTED_COMPLETION, $videoId, $targetId, 0, json_encode([
            'type'  => 2,
            'value' => '3 days',
        ]));
        list($type, $value) = LoHelper::getSuggestedCompletion($this->go1, $videoId, $this->module1Id);
        $this->assertEquals($type, LoSuggestedCompletionTypes::E_DURATION);
        $this->assertEquals($value, '3 days');
    }

    public function testLoadTree()
    {
        $courseId = $this->createCourse($this->go1);
        $moduleId = $this->createModule($this->go1);
        $videoId = $this->createVideo($this->go1);
        $eventId = $this->createLO($this->go1, ['type' => LiTypes::EVENT]);

        $this->link($this->go1, EdgeTypes::HAS_MODULE, $courseId, $moduleId);
        $this->link($this->go1, EdgeTypes::HAS_LI, $courseId, $eventId);
        $this->link($this->go1, EdgeTypes::HAS_LI, $moduleId, $videoId);

        $course = LoHelper::load($this->go1, $courseId, null, true);
        $this->assertEquals($eventId, $course->items[0]->id);
        $this->assertEquals($moduleId, $course->items[1]->id);
        $this->assertEquals($videoId, $course->items[1]->items[0]->id);
    }

    public function testModuleIds()
    {
        # Course 1
        $moduleIds = LoHelper::moduleIds($this->go1, $this->course1Id);
        $this->assertEquals(1, count($moduleIds));
        $this->hasChild($this->module1Id, $moduleIds);

        # Course 2
        $moduleIds = LoHelper::moduleIds($this->go1, $this->course2Id);
        $this->assertEquals(1, count($moduleIds));
        $this->hasChild($this->module2Id, $moduleIds);
    }

    public function dataLi()
    {
        return [
            [[
                 LiTypes::VIDEO,
             ], 1,
            ],
            [[
                 LiTypes::VIDEO, LiTypes::EVENT,
             ], 2,
            ],
            [[
                 LiTypes::VIDEO, LiTypes::EVENT, LiTypes::EVENT,
             ], 2,
            ],
        ];
    }

    /**
     * @dataProvider dataLi
     */
    public function testCountChild($liTypes, $liNumber)
    {
        $courseId = $this->createCourse($this->go1);
        $moduleId = $this->createModule($this->go1);
        $this->link($this->go1, EdgeTypes::HAS_MODULE, $courseId, $moduleId);

        $step = 1;
        foreach ($liTypes as $type) {
            $liId = $this->createLO($this->go1, ['title' => 'ばか' . $type . $step, 'type' => $type]);
            $this->link($this->go1, EdgeTypes::HAS_LI, $moduleId, $liId);
            $step++;
        }

        $countChild = LoHelper::countChild($this->go1, $courseId);
        $this->assertEquals($liNumber, $countChild);
    }

    public function testAllowReuseEnrolment()
    {
        $courseId = $this->createCourse($this->go1, ['instance_id' => $this->createPortal($this->go1, []), 'data' => [LoHelper::ALLOW_REUSE_ENROLMENT => true]]);
        $course = LoHelper::load($this->go1, $courseId);
        $this->assertTrue(LoHelper::allowReuseEnrolment($course));
        $this->assertFalse(LoHelper::allowReuseEnrolment(LoHelper::load($this->go1, $this->course1Id)));
    }

    public function testPremiumFlag()
    {
        $courseId = $this->createCourse($this->go1, ['instance_id' => $this->createPortal($this->go1, []), 'premium' => 1]);
        $course = LoHelper::load($this->go1, $courseId);
        $this->assertEquals($course->premium, 1);
    }

    public function testAttributes()
    {
        $dimensionType = 2;
        $this->createAttributeLookup($this->go1, LoAttributes::REGION_RESTRICTIONS, LoAttributes::machineName(LoAttributes::REGION_RESTRICTIONS), 'DIMENSION', 'video',
            '["ALWAYS", "FOR_PUBLISH"]', '["Author"]', null, 1, $dimensionType);

        $this->go1->insert('dimensions', [
            'id'             => 3,
            'parent_id'      => 0,
            'name'           => "NAME",
            'type'           => $dimensionType,
            'created_date'   => 0,
            'modified_date'   => 0
        ]);
        $loId = $this->createLO($this->go1, [
                'instance_id' => $this->createPortal($this->go1, []),
                'type' => 'video',
                'attributes' => [
                    LoAttributes::machineName(LoAttributes::REGION_RESTRICTIONS) => [
                        [ "key" => "3", "value" => "" ],
                    ]
                ]
            ]);

        $lo = LoHelper::load($this->go1, $loId, null, false, true);
        $this->assertObjectHasAttribute(LoAttributes::machineName(LoAttributes::REGION_RESTRICTIONS), $lo->attributes);
    }

    public function testAttributeLearningOutcomes()
    {
        $this->createAttributeLookup($this->go1, LoAttributes::LEARNING_OUTCOMES, LoAttributes::machineName(LoAttributes::LEARNING_OUTCOMES), 'TEXT', 'video',
            '["NO"]', '[]', null, 1, null);

        $body = [LoAttributes::machineName(LoAttributes::LEARNING_OUTCOMES) => [
            "This is alearning, outcome",
            "Woahzers Rick, that 1 value is really something.",
            "Listen up Mo-*Burp*rty, you are gonna learn today!",
            "123123",
            "123123"
        ]];

        $loId = $this->createLO($this->go1, [
            'instance_id' => $this->createPortal($this->go1, []),
            'type' => 'video',
            'attributes' => $body
        ]);

        $lo = LoHelper::load($this->go1, $loId, null, false, true);
        $this->assertObjectHasAttribute(LoAttributes::machineName(LoAttributes::LEARNING_OUTCOMES), $lo->attributes);
    }

    public function testSummary()
    {
        $courseId = $this->createCourse($this->go1, ['instance_id' => $this->createPortal($this->go1, []), 'summary' => "a summary"]);
        $course = LoHelper::load($this->go1, $courseId);
        $this->assertEquals($course->summary, "a summary");
    }

    public function testSanitizeTitle() {
        $title = "<strong>Strong</strong> Test Title & &amp &lt; <br> <br/> 

    After New line
         ";
        $sanitizedTitle = LoHelper::sanitizeTitle($title);
        $this->assertEquals($sanitizedTitle, "Strong Test Title & &amp <     After New line");
    }
}
