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
        $id = $this->createQuiz($this->go1, ['title' => 'demo quiz', 'li_id' => 10]);
        $this->quiz = QuizHelper::load($this->go1, $id);
        $this->assertFalse(QuizHelper::load($this->go1, 0));
        $this->assertEquals('demo quiz', QuizHelper::load($this->go1, $this->quiz->quiz_id)->title);
        $this->assertEquals('demo quiz', QuizHelper::loadByLiId($this->go1, 10)->title);
    }

    public function testQuestionCount()
    {
        $this->createQuizQuestion($this->go1, ['quiz_ruuid' => $this->quiz->ruuid]);
        $this->createQuizQuestion($this->go1, ['quiz_ruuid' => $this->quiz->ruuid]);

        $this->assertEquals(2, QuizHelper::questionCount($this->go1, $this->quiz));
        $this->createQuizQuestion($this->go1, ['quiz_ruuid' => $this->quiz->ruuid]);
        $this->assertEquals(3, QuizHelper::questionCount($this->go1, $this->quiz));
    }

    public function testAnswerCountByEnrolment()
    {
        $enrolmentId = $this->createEnrolment($this->go1, []);
        $rid = $this->createQuizResult($this->go1, ['enrolment_id' => $enrolmentId]);
        $result = QuizHelper::loadResult($this->go1, $rid);
        $this->createQuizSequence($this->go1, ['result_uuid' => $result->uuid]);

        $this->assertEquals(1, QuizHelper::answerCountByEnrolment($this->go1, $enrolmentId));
        $this->createQuizSequence($this->go1, ['result_uuid' => $result->uuid]);
        $this->assertEquals(2, QuizHelper::answerCountByEnrolment($this->go1, $enrolmentId));
    }

    public function testProgress()
    {
        $enrolmentId = $this->createEnrolment($this->go1, []);
        $rid = $this->createQuizResult($this->go1, ['enrolment_id' => $enrolmentId]);
        $result = QuizHelper::loadResult($this->go1, $rid);

        $this->createQuizQuestion($this->go1, ['quiz_ruuid' => $this->quiz->ruuid]);
        $this->createQuizQuestion($this->go1, ['quiz_ruuid' => $this->quiz->ruuid]);
        $this->createQuizQuestion($this->go1, ['quiz_ruuid' => $this->quiz->ruuid]);
        $this->createQuizQuestion($this->go1, ['quiz_ruuid' => $this->quiz->ruuid]);
        $this->createQuizSequence($this->go1, ['result_uuid' => $result->uuid]);

        $progress = QuizHelper::progress($this->go1, $this->quiz, $enrolmentId);
        $this->assertEquals(25, $progress[EnrolmentStatuses::PERCENTAGE]);
        $this->assertEquals(1, $progress[EnrolmentStatuses::COMPLETED]);

        $this->createQuizSequence($this->go1, ['result_uuid' => $result->uuid]);
        $progress = QuizHelper::progress($this->go1, $this->quiz, $enrolmentId);
        $this->assertEquals(50, $progress[EnrolmentStatuses::PERCENTAGE]);
        $this->assertEquals(2, $progress[EnrolmentStatuses::COMPLETED]);
    }
}
