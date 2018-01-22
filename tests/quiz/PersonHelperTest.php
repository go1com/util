<?php

namespace go1\util\tests\quiz;

use go1\util\quiz\PersonHelper;
use go1\util\schema\mock\QuizMockTrait;
use go1\util\tests\UtilTestCase;

class PersonHelperTest extends UtilTestCase
{
    use QuizMockTrait;

    public function testLoadByExternalId()
    {
        $id = $this->createQuizPerson($this->db, ['external_identifier' => 123, 'external_source' => 'go1.user']);

        $this->assertTrue(is_object(PersonHelper::loadByExternalId($this->db, 123)));
        $this->assertFalse(PersonHelper::loadByExternalId($this->db, 123, 'other-source'));
        $this->assertFalse(PersonHelper::loadByExternalId($this->db, 125));
    }

    public function testLoadBySecondaryId()
    {
        $id = $this->createQuizPerson($this->db, ['secondary_identifier' => 123, 'external_source' => 'go1.user']);

        $this->assertTrue(is_object(PersonHelper::loadBySecondaryId($this->db, 123)));
        $this->assertFalse(PersonHelper::loadBySecondaryId($this->db, 123, 'other-source'));
        $this->assertFalse(PersonHelper::loadBySecondaryId($this->db, 125));
    }
}
