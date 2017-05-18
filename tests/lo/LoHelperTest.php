<?php

namespace go1\util\tests\lo;

use DateTime;
use go1\util\edge\EdgeTypes;
use go1\util\lo\LiTypes;
use go1\util\lo\LoHelper;
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
            'Plain text'                                   => 'Plain text',
            'foo <span style="color:#0000aa;">data</span>' => 'foo <span style="color:#0000aa;">data</span>',
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

    public function testLoadEventLegacy()
    {
        $courseId = $this->createCourse($this->db);
        $this->db->update('gc_lo',
            ['event' => json_encode([
                'start' => $start = (new DateTime('+1 day'))->format(DATE_ISO8601),
                'end'   => $end = (new DateTime('+2 days'))->format(DATE_ISO8601),
            ])],
            ['id' => $courseId]
        );
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
        $this->assertEquals(0, count($authors));

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
        $this->assertEquals(0, count($authors));

        # Resource 1
        $authors = LoHelper::parentsAuthorIds($this->db, $this->resource1Id);
        $this->assertEquals(3, count($authors));
        $this
            ->hasAuthor($this->author1Id, $authors)
            ->hasAuthor($this->author2Id, $authors)
            ->hasAuthor($this->author4Id, $authors);

        # Resource 2
        $authors = LoHelper::parentsAuthorIds($this->db, $this->resource2Id);
        $this->assertEquals(1, count($authors));
        $this->hasAuthor($this->author4Id, $authors);
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
        $this->assertEquals(2, count($parentIds));
        $this
            ->hasParent($this->module1Id, $parentIds)
            ->hasParent($this->module2Id, $parentIds);

        # Resource 2
        $parentIds = LoHelper::parentIds($this->db, $this->resource2Id);
        $this->assertEquals(1, count($parentIds));
        $this->hasParent($this->module2Id, $parentIds);
    }

    public function testChildIds()
    {
        # Course 1
        $childIds = LoHelper::childIds($this->db, $this->course1Id);
        $this->assertEquals(1, count($childIds));
        $this->hasChild($this->module1Id, $childIds);

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

    private function hasAuthor($authorId, array $source)
    {
        $this->assertTrue(in_array($authorId, $source));
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
