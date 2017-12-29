<?php

namespace go1\util\tests\quiz;

use go1\util\enrolment\EnrolmentStatuses;
use go1\util\quiz\QuizHelper;
use go1\util\schema\mock\EnrolmentMockTrait;
use go1\util\schema\mock\QuizMockTrait;
use go1\util\tests\UtilTestCase;

class QuizHelperTest extends UtilTestCase
{
    use QuizMockTrait;
    use EnrolmentMockTrait;
    private $quiz;

    public function setUp()
    {
        parent::setUp();
        $id = $this->createQuiz($this->db, ['title' => 'demo quiz', 'li_id' => 10]);
        $this->quiz = QuizHelper::load($this->db, $id);
        $this->assertFalse(QuizHelper::load($this->db, 0));
        $this->assertEquals('demo quiz', QuizHelper::load($this->db, $this->quiz->quiz_id)->title);
        $this->assertEquals('demo quiz', QuizHelper::loadByLiId($this->db, 10)->title);
    }

    public function testQuestionCount()
    {
        $this->createQuizQuestion($this->db, ['quiz_ruuid'=> $this->quiz->ruuid]);
        $this->createQuizQuestion($this->db, ['quiz_ruuid'=> $this->quiz->ruuid]);

        $this->assertEquals(2, QuizHelper::questionCount($this->db, $this->quiz));
        $this->createQuizQuestion($this->db, ['quiz_ruuid'=> $this->quiz->ruuid]);
        $this->assertEquals(3, QuizHelper::questionCount($this->db, $this->quiz));
    }

    public function testAnswerCountByEnrolment()
    {
        $enrolmentId = $this->createEnrolment($this->db, []);
        $rid = $this->createQuizResult($this->db, ['enrolment_id' => $enrolmentId]);
        $result = QuizHelper::loadResult($this->db, $rid);
        $this->createQuizSequence($this->db, ['result_uuid' => $result->uuid]);

        $this->assertEquals(1, QuizHelper::answerCountByEnrolment($this->db, $enrolmentId));
        $this->createQuizSequence($this->db, ['result_uuid' => $result->uuid]);
        $this->assertEquals(2, QuizHelper::answerCountByEnrolment($this->db, $enrolmentId));
    }

    public function testProgress()
    {
        $enrolmentId = $this->createEnrolment($this->db, []);
        $rid = $this->createQuizResult($this->db, ['enrolment_id' => $enrolmentId]);
        $result = QuizHelper::loadResult($this->db, $rid);

        $this->createQuizQuestion($this->db, ['quiz_ruuid'=> $this->quiz->ruuid]);
        $this->createQuizQuestion($this->db, ['quiz_ruuid'=> $this->quiz->ruuid]);
        $this->createQuizQuestion($this->db, ['quiz_ruuid'=> $this->quiz->ruuid]);
        $this->createQuizQuestion($this->db, ['quiz_ruuid'=> $this->quiz->ruuid]);
        $this->createQuizSequence($this->db, ['result_uuid' => $result->uuid]);

        $progress = QuizHelper::progress($this->db, $this->quiz, $enrolmentId);
        $this->assertEquals(25, $progress[EnrolmentStatuses::PERCENTAGE]);
        $this->assertEquals(1, $progress[EnrolmentStatuses::COMPLETED]);

        $this->createQuizSequence($this->db, ['result_uuid' => $result->uuid]);
        $progress = QuizHelper::progress($this->db, $this->quiz, $enrolmentId);
        $this->assertEquals(50, $progress[EnrolmentStatuses::PERCENTAGE]);
        $this->assertEquals(2, $progress[EnrolmentStatuses::COMPLETED]);
    }
}
