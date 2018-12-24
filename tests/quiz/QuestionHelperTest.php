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
        $id = $this->createQuestion($this->go1);

        $this->assertTrue(is_object(QuestionHelper::load($this->go1, $id)));
        $this->assertFalse(QuestionHelper::load($this->go1, 123));
    }
}
