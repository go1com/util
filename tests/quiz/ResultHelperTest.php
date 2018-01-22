<?php

namespace go1\util\tests\assignment;

use go1\util\quiz\ResultHelper;
use go1\util\schema\mock\QuizMockTrait;
use go1\util\tests\UtilTestCase;

class ResultHelperTest extends UtilTestCase
{
    use QuizMockTrait;

    public function testLoad()
    {
        $id = $this->createQuizResult($this->db);

        $this->assertTrue(is_object(ResultHelper::load($this->db, $id)));
        $this->assertFalse(ResultHelper::load($this->db, 123));
    }

    public function testGetSubmittedDate()
    {
        $ruuid = 'abc-123';
        $this->createQuiz($this->db, ['ruuid' => $ruuid]);
        $this->createQuizResult($this->db, ['taker' => 111, 'quiz_ruuid' => $ruuid, 'time_start' => 345]);
        $this->createQuizResult($this->db, ['taker' => 111, 'quiz_ruuid' => $ruuid, 'time_start' => 123]);
        $this->createQuizResult($this->db, ['taker' => 111, 'quiz_ruuid' => $ruuid, 'time_start' => 234]);
        $this->createQuizResult($this->db, ['taker' => 112, 'quiz_ruuid' => $ruuid, 'time_start' => 999]);

        $this->assertEquals(234, ResultHelper::getSubmittedDate($this->db, 111, $ruuid));
    }

    public function testGetMarkedDateNull()
    {
        $ruuid = 'abc-123';
        $this->createQuiz($this->db, ['ruuid' => $ruuid]);
        $this->createQuizResult($this->db, ['taker' => 111, 'quiz_ruuid' => $ruuid, 'is_evaluated' => 0, 'time_end' => 345]);
        $this->createQuizResult($this->db, ['taker' => 111, 'quiz_ruuid' => $ruuid, 'is_evaluated' => 0, 'time_end' => 234]);
        $this->createQuizResult($this->db, ['taker' => 111, 'quiz_ruuid' => $ruuid, 'is_evaluated' => 1, 'time_end' => 121]);
        $this->createQuizResult($this->db, ['taker' => 111, 'quiz_ruuid' => $ruuid, 'is_evaluated' => 1, 'time_end' => 56]);
        $this->createQuizResult($this->db, ['taker' => 111, 'quiz_ruuid' => $ruuid, 'is_evaluated' => 0, 'time_end' => 87]);
        $this->createQuizResult($this->db, ['taker' => 112, 'quiz_ruuid' => $ruuid, 'is_evaluated' => 1, 'time_end' => 998]);

        $this->assertEquals(null, ResultHelper::getMarkedDate($this->db, 111, $ruuid));
    }

    public function testGetMarkedDate()
    {
        $ruuid = 'abc-123';
        $this->createQuiz($this->db, ['ruuid' => $ruuid]);
        $this->createQuizResult($this->db, ['taker' => 111, 'quiz_ruuid' => $ruuid, 'is_evaluated' => 0, 'time_end' => 345]);
        $this->createQuizResult($this->db, ['taker' => 111, 'quiz_ruuid' => $ruuid, 'is_evaluated' => 0, 'time_end' => 234]);
        $this->createQuizResult($this->db, ['taker' => 111, 'quiz_ruuid' => $ruuid, 'is_evaluated' => 1, 'time_end' => 121]);
        $this->createQuizResult($this->db, ['taker' => 111, 'quiz_ruuid' => $ruuid, 'is_evaluated' => 1, 'time_end' => 56]);
        $this->createQuizResult($this->db, ['taker' => 112, 'quiz_ruuid' => $ruuid, 'is_evaluated' => 1, 'time_end' => 998]);

        $this->assertEquals(56, ResultHelper::getMarkedDate($this->db, 111, $ruuid));
    }
}
