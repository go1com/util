<?php

namespace go1\util\schema\mock;

use Doctrine\DBAL\Connection;

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
            'created'              => isset($options['created']) ? $options['created'] : time() * 1000,
        ]);

        return $db->lastInsertId('person');
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
}
