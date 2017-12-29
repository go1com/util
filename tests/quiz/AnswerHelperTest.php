<?php

namespace go1\util\tests\quiz;

use go1\util\quiz\AnswerHelper;
use go1\util\schema\mock\QuizMockTrait;
use go1\util\tests\UtilTestCase;

class AnswerHelperTest extends UtilTestCase
{
    use QuizMockTrait;

    public function testLoad()
    {
        $id = $this->createQuizUserAnswer($this->db);

        $this->assertTrue(is_object(AnswerHelper::load($this->db, $id)));
        $this->assertFalse(AnswerHelper::load($this->db, 123));
    }

    public function testLoadByQuestionUuid()
    {
        $this->createQuizUserAnswer($this->db, ['question_ruuid' => 123]);
        $this->createQuizUserAnswer($this->db, ['question_ruuid' => 234]);

        $this->assertTrue(is_object(AnswerHelper::loadByQuestionRuuid($this->db, 123)));
        $this->assertTrue(is_object(AnswerHelper::loadByQuestionRuuid($this->db, 234)));
        $this->assertFalse(AnswerHelper::loadByQuestionRuuid($this->db, 345));
    }
}
