<?php

namespace go1\util\schema\mock;

use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;

trait QuizMockTrait
{
    protected function createQuizUserAnswer(Connection $db, array $options = [])
    {
        $db->insert('answer', [
            'uuid'              => isset($options['uuid']) ? $options['uuid'] : '',
            'question_uuid'     => isset($options['question_uuid']) ? $options['question_uuid'] : '',
            'question_ruuid'    => isset($options['question_ruuid']) ? $options['question_ruuid'] : '',
            'question_type'     => isset($options['question_type']) ? $options['question_type'] : '',
            'is_correct'        => isset($options['is_correct']) ? $options['is_correct'] : 0,
            'is_skipped'        => isset($options['is_skipped']) ? $options['is_skipped'] : 0,
            'is_evaluated'      => isset($options['is_evaluated']) ? $options['is_evaluated'] : 0,
            'points'            => isset($options['points']) ? $options['points'] : null,
            'answer_timestamp'  => isset($options['answer_timestamp']) ? $options['answer_timestamp'] : time() * 1000,
            'changed'           => isset($options['changed']) ? $options['changed'] : time() * 1000,
            'taker'             => isset($options['taker']) ? $options['taker'] : 0,
            'answer'            => isset($options['answer']) ? $options['answer'] : null,
        ]);

        return $db->lastInsertId('answer');
    }

    protected function createQuizPerson(Connection $db, array $options = [])
    {
        $db->insert('person', [
            'external_source'      => isset($options['external_source']) ? $options['external_source'] : 'go1.user',
            'external_identifier'  => isset($options['external_identifier']) ? $options['external_identifier'] : 0,
            'secondary_identifier' => isset($options['secondary_identifier']) ? $options['secondary_identifier'] : 0,
            'mail'                 => isset($options['mail']) ? $options['mail'] : '',
            'created'              => isset($options['created']) ? $options['created'] : time() * 1000,
            'custom'               => isset($options['custom']) ? $options['custom'] : '',
        ]);

        return $db->lastInsertId('person');
    }

    protected function createQuestion(Connection $db, array $options = [])
    {
        $db->insert('question', [
            'question_id'   => isset($options['question_id']) ? $options['question_id'] : 0,
            'question_type' => isset($options['question_type']) ? $options['question_type'] : '',
            'uuid'          => isset($options['uuid']) ? $options['uuid'] : '',
            'ruuid'         => isset($options['ruuid']) ? $options['ruuid'] : '',
            'title'         => isset($options['title']) ? $options['title'] : '',
            'description'   => isset($options['description']) ? $options['description'] : '',
            'feedback'      => isset($options['feedback']) ? $options['feedback'] : '',
            'status'        => isset($options['status']) ? $options['status'] : 1,
            'created'       => isset($options['created']) ? $options['created'] : time() * 1000,
            'changed'       => isset($options['changed']) ? $options['changed'] : time() * 1000,
            'editor'        => isset($options['editor']) ? $options['editor'] : 0,
            'data'          => isset($options['data']) ? $options['data'] : '',
            'config'        => isset($options['config']) ? $options['config'] : '',
            'li_id'         => isset($options['li_id']) ? $options['li_id'] : 0,
        ]);

        return $db->lastInsertId('question');
    }

    protected function createQuizQuestionRevision(Connection $db, array $options = [])
    {
        $db->insert('question_revisions', [
            'question_id'       => isset($options['question_id']) ? $options['question_id'] : 0,
            'question_type'     => isset($options['question_type']) ? $options['question_type'] : '',
            'ruuid'             => isset($options['ruuid']) ? $options['ruuid'] : '',
            'uuid'              => isset($options['uuid']) ? $options['uuid'] : '',
            'title'             => isset($options['title']) ? $options['title'] : '',
            'description'       => isset($options['description']) ? $options['description'] : null,
            'feedback'          => isset($options['feedback']) ? $options['feedback'] : null,
            'status'            => isset($options['status']) ? $options['status'] : 1,
            'created'           => isset($options['created']) ? $options['created'] : time() * 1000,
            'editor'            => isset($options['editor']) ? $options['editor'] : 0,
            'data'              => isset($options['data']) ? $options['data'] : null,
            'config'            => isset($options['config']) ? $options['config'] : null,
        ]);

        return $db->lastInsertId('question_revisions');
    }

    protected function createQuizResult(Connection $db, array $options = [])
    {
        static $id = 1;
        $data = isset($options['data']) ? $options['data'] : '[]';
        $data = is_scalar($data) ? $data : json_encode($data);

        $db->insert('result', [
            'result_id'     => isset($options['result_id']) ? $options['result_id'] : $id++,
            'uuid'          => $options['uuid'] ?? Uuid::uuid4()->toString(),
            'time_start'    => $options['time_start'] ?? null,
            'time_end'      => $options['time_end'] ?? null,
            'score'         => $options['score'] ?? 0,
            'is_evaluated'  => $options['is_evaluated'] ?? 0,
            'last_sequence' => $options['last_sequence'] ?? '',
            'time_left'     => $options['time_left'] ?? 0,
            'taker'         => $options['taker'] ?? 0,
            'quiz_uuid'     => $options['quiz_uuid'] ?? Uuid::uuid4()->toString(),
            'quiz_ruuid'    => $options['quiz_ruuid'] ?? Uuid::uuid4()->toString(),
            'is_best'       => $options['is_best'] ?? 0,
            'outcome'       => $options['outcome'] ?? '',
            'enrolment_id'  => $options['enrolment_id'] ?? null,
            'counter'       => $options['counter'] ?? 0,
            'cross_counter' => $options['cross_counter'] ?? 0,
            #'created'       => $options['created'] ?? 0,
            #'changed'       => $options['changed'] ?? 0,
            #'time_close'    => $options['time_close'] ?? 0,
            #'is_manual'     => $options['is_manual'] ?? 0,
            #'status'        => $options['status'] ?? 0,
            'data'          => $data,
        ]);

        return $db->lastInsertId('result');
    }

    protected function createQuizQuestion(Connection $db, array $options = [])
    {
        static $id = 1;
        $db->insert('quiz_questions', [
            'relationship_id' => isset($options['relationship_id']) ? $options['relationship_id'] : $id++,
            'quiz_uuid'       => $options['quiz_uuid'] ?? Uuid::uuid4()->toString(),
            'question_uuid'   => $options['question_uuid'] ?? Uuid::uuid4()->toString(),
            'weight'          => $options['weight'] ?? 0,
            'max_score'       => $options['max_score'] ?? 0,
            'editor'          => $options['editor'] ?? 0,
            'uuid'            => $options['uuid'] ?? '',
            'quiz_ruuid'      => $options['quiz_ruuid'] ?? Uuid::uuid4()->toString(),
            'question_ruuid'  => $options['question_ruuid'] ?? '',
            'created'         => $options['created'] ?? 0,
            'changed'         => $options['changed'] ?? 0,
            'pass_rate'       => $options['pass_rate'] ?? 0,
        ]);

        return $db->lastInsertId('quiz_questions');
    }

    protected function createQuiz(Connection $db, array $options = [])
    {
        static $id = 1;
        $data = isset($options['data']) ? $options['data'] : '[]';
        $data = is_scalar($data) ? $data : json_encode($data);

        $db->insert('quiz', [
            'quiz_id'              => isset($options['quiz_id']) ? $options['quiz_id'] : $id++,
            'uuid'                 => $options['uuid'] ?? Uuid::uuid4()->toString(),
            'title'                => $options['title'] ?? 'Foo',
            'description'          => $options['description'] ?? '',
            'status'               => $options['status'] ?? 0,
            'created'              => $options['created'] ?? 0,
            'changed'              => $options['changed'] ?? 0,
            'pass_rate'            => $options['pass_rate'] ?? 0,
            'summary_pass'         => $options['summary_pass'] ?? '',
            'summary_default'      => $options['summary_default'] ?? '',
            'randomization'        => $options['randomization'] ?? 0,
            'backwards_navigation' => $options['backwards_navigation'] ?? 0,
            'keep_results'         => $options['keep_results'] ?? 0,
            'repeat_until_correct' => $options['repeat_until_correct'] ?? 0,
            'feedback_time'        => $options['feedback_time'] ?? 0,
            'display_feedback'     => $options['display_feedback'] ?? 0,
            'quiz_open'            => $options['quiz_open'] ?? null,
            'quiz_close'           => $options['quiz_close'] ?? null,
            'takes'                => $options['takes'] ?? 0,
            'time_limit'           => $options['time_limit'] ?? 0,
            'max_score'            => $options['max_score'] ?? 0,
            'allow_skipping'       => $options['allow_skipping'] ?? 0,
            'allow_resume'         => $options['allow_resume'] ?? 0,
            'allow_jumping'        => $options['allow_jumping'] ?? 0,
            'editor'               => $options['editor'] ?? 0,
            'ruuid'                => $options['ruuid'] ?? Uuid::uuid4()->toString(),
            'taken'                => $options['taken'] ?? 0,
            'is_redo_correct'      => $options['is_redo_correct'] ?? 0,
            'li_id'                => $options['li_id'] ?? 0,
            #'in_progress_attempts' => $options['in_progress_attempts'] ?? 0,
            #'show_answers_after'   => $options['show_answers_after'] ?? 0,
            'data'                 => $data,
        ]);

        return $db->lastInsertId('quiz');
    }

    protected function createQuizSequence(Connection $db, array $options = [])
    {
        static $id = 1;
        $data = isset($options['data']) ? $options['data'] : '[]';
        $data = is_scalar($data) ? $data : json_encode($data);

        $db->insert('sequence', [
            'sequence_id'    => isset($options['sequence_id']) ? $options['sequence_id'] : $id++,
            'uuid'           => $options['uuid'] ?? Uuid::uuid4()->toString(),
            'delta'          => $options['delta'] ?? 0,
            'is_backable'    => $options['is_backable'] ?? 0,
            'is_finishable'  => $options['is_finishable'] ?? 0,
            'is_jumpable'    => $options['is_jumpable'] ?? 0,
            'is_answerable'  => $options['is_answerable'] ?? 0,
            'is_skippable'   => $options['is_skippable'] ?? 0,
            'next_sequence'  => $options['next_sequence'] ?? '',
            'prev_sequence'  => $options['prev_sequence'] ?? '',
            'taker'          => $options['taker'] ?? 0,
            'total'          => $options['total'] ?? 0,
            'created'        => $options['created'] ?? 0,
            'quiz_ruuid'     => $options['quiz_ruuid'] ?? '',
            'question_ruuid' => $options['question_ruuid'] ?? '',
            'answer_uuid'    => $options['answer_uuid'] ?? '',
            'result_uuid'    => $options['result_uuid'] ?? '',
            'relation_uuid'  => $options['relation_uuid'] ?? '',
            #'enrolment_id'   => $options['enrolment_id'] ?? 0,
            'data'           => $data,
        ]);

        return $db->lastInsertId('sequence');
    }
}
