<?php

namespace go1\util\tests;

use go1\util\edge\EdgeTypes;
use go1\util\lo\LoHelper;
use go1\util\schema\mock\InstanceMockTrait;
use go1\util\schema\mock\LoMockTrait;
use go1\util\schema\mock\UserMockTrait;
use HTMLPurifier;

class LoHelperTest extends UtilTestCase
{
    use UserMockTrait;
    use LoMockTrait;
    use InstanceMockTrait;

    public function testAssessor()
    {
        $courseId = $this->createCourse($this->db, ['instance_id' => $this->createInstance($this->db, [])]);
        $assessor1Id = $this->createUser($this->db, ['mail' => 'assessor1@mail.com']);
        $assessor2Id = $this->createUser($this->db, ['mail' => 'assessor2@mail.com']);
        $this->createUser($this->db, ['mail' => 'assessor3@mail.com']);

        $this->link($this->db, EdgeTypes::COURSE_ASSESSOR, $courseId, $assessor1Id);
        $this->link($this->db, EdgeTypes::COURSE_ASSESSOR, $courseId, $assessor2Id);

        $assessors = LoHelper::assessorIds($this->db, $courseId);
        $this->assertEquals(2, count($assessors));
        $this->assertEquals($assessor1Id, $assessors[0]);
        $this->assertEquals($assessor2Id, $assessors[1]);
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
            'Plain text' => 'Plain text',
            'foo <span style="color:#0000aa;">data</span>' => 'foo <span style="color:#0000aa;">data</span>'
        ];
        foreach ($data as $input => $expect) {
            $result = $html->purify(trim($input), LoHelper::descriptionPurifierConfig());
            $this->assertEquals($expect, $result);
        }
    }
}
