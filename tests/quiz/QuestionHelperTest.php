<?php

namespace go1\util\tests\quiz;

use go1\util\quiz\QuestionHelper;
use go1\util\schema\mock\QuizMockTrait;
use go1\util\tests\UtilTestCase;

class QuestionHelperTest extends UtilTestCase
{
    use QuizMockTrait;

    public function testLoad()
    {
        $id = $this->createQuestion($this->db);

        $this->assertTrue(is_object(QuestionHelper::load($this->db, $id)));
        $this->assertFalse(QuestionHelper::load($this->db, 123));
    }
}
