<?php

namespace go1\util\schema;

use Doctrine\DBAL\Schema\Schema;

class QuizSchema
{
    public static function install(Schema $schema)
    {
        // quiz table
        $tbl = $schema->createTable('quiz');
        $tbl->addColumn('quiz_id', 'integer', ['unsigned' => true, 'notnull' => true, 'autoincrement' => true, 'comment' => 'The primary identifier of a quiz.']);
        $tbl->addColumn('quiz_rid', 'integer', ['unsigned' => true, 'notnull' => false, 'default' => null, 'comment' => 'The current quiz_revisions.quiz_rid version identifier.']);
        $tbl->addColumn('uuid', 'string', ['notnull' => true, 'length' => 36, 'default' => '', 'comment' => 'The universally unique identifier.']);
        $tbl->addColumn('ruuid', 'string', ['notnull' => false, 'length' => 36, 'default' => '', 'comment' => 'Quiz revision uuid.']);
        $tbl->addColumn('title', 'string', ['length' => 255, 'notnull' => true, 'default' => '', 'comment' => 'The title of this quiz.']);
        $tbl->addColumn('description', 'text', ['notnull' => false, 'default' => null, 'comment' => 'The description of this quiz.']);
        $tbl->addColumn('status', 'boolean', ['default' => 1, 'comment' => 'Boolean indicating whether the quiz is published.']);
        $tbl->addColumn('created', 'bigint', ['notnull' => true, 'default' => 0, 'comment' => 'The unix timestamp when the quiz was created.']);
        $tbl->addColumn('changed', 'bigint', ['notnull' => true, 'default' => 0, 'comment' => 'The unix timestamp when the quiz was most recently saved.']);
        $tbl->addColumn('pass_rate', 'smallint', ['unsigned' => true, 'notnull' => true, 'comment' => 'Passing rate out of 100.']);
        $tbl->addColumn('summary_pass', 'string', ['length' => 255, 'notnull' => false, 'comment' => 'Summary text for a passing grade.']);
        $tbl->addColumn('summary_default', 'string', ['length' => 255, 'notnull' => false, 'comment' => 'Summary text for any grade.']);
        $tbl->addColumn('randomization', 'boolean', ['default' => 0, 'comment' => 'Boolean field indicating if this quiz has random question order.']);
        $tbl->addColumn('backwards_navigation', 'boolean', ['default' => 1, 'comment' => 'Boolean indicating whether a quiz taker can navigate backwards.']);
        $tbl->addColumn('keep_results', 'smallint', ['unsigned' => true, 'notnull' => true, 'comment' => 'Enumerated field indicating if this quiz should prune old results. 0=only keep best, 1=only keep newest, 2=keep all']);
        $tbl->addColumn('repeat_until_correct', 'boolean', ['default' => 0, 'comment' => 'Boolean indicating whether a quiz taker must repeat the question until selecting the correct answer.']);
        $tbl->addColumn('feedback_time', 'smallint', ['unsigned' => true, 'default' => 2, 'comment' => 'Enumerated field indicating when to show feedback. 0=end of quiz, 1=end each question, 2=not show']);
        $tbl->addColumn('display_feedback', 'boolean', ['default' => 1]);
        $tbl->addColumn('quiz_open', 'datetimetz', ['notnull' => false, 'comment' => 'Datetime when the quiz open.']);
        $tbl->addColumn('quiz_close', 'datetimetz', ['notnull' => false, 'comment' => 'Datetime when the quiz close.']);
        $tbl->addColumn('takes', 'smallint', ['unsigned' => true, 'notnull' => true, 'default' => 0, 'comment' => 'Limit the number of times this quiz can be taken by a learner. 0 for unlimited.']);
        $tbl->addColumn('time_limit', 'integer', ['unsigned' => true, 'notnull' => false, 'comment' => 'Number of seconds for a user to complete an attempt.']);
        $tbl->addColumn('max_score', 'integer', ['notnull' => true, 'default' => 0, 'comment' => 'The max score of this quiz.']);
        $tbl->addColumn('allow_skipping', 'boolean', ['default' => 0, 'comment' => 'Boolean indicating whether the user can skip a question.']);
        $tbl->addColumn('allow_resume', 'boolean', ['default' => 1, 'comment' => 'Boolean indicating whether a user can resume a quiz after logging out and in.']);
        $tbl->addColumn('allow_jumping', 'boolean', ['default' => 0, 'comment' => 'Boolean indicating whether a user can skip to any question.']);
        $tbl->addColumn('taken', 'boolean', ['default' => 0, 'comment' => 'Boolean indicating whether the user can taken a quiz.']);
        $tbl->addColumn('is_redo_correct', 'boolean', ['default' => 1, 'comment' => 'Boolean indicating whether user has to redo correct answers.']);
        $tbl->addColumn('editor', 'integer', ['unsigned' => true, 'notnull' => true, 'comment' => 'Person who created this quiz']);
        $tbl->addColumn('li_id', 'integer', ['notnull' => false, 'default' => null, 'comment' => 'Learning item id']);
        $tbl->addColumn('data', 'text', ['notnull' => false, 'default' => null, 'comment' => 'Json encoded extra data.']);
        $tbl->setPrimaryKey(['quiz_id']);
        $tbl->addUniqueIndex(['quiz_rid'], 'unq_quiz_quiz_rid');
        $tbl->addIndex(['uuid'], 'idx_quiz_uuid');

        // quiz_revisions table
        $tbl = $schema->createTable('quiz_revisions');
        $tbl->addColumn('quiz_rid', 'integer', ['unsigned' => true, 'notnull' => true, 'autoincrement' => true, 'comment' => 'The primary identifier for this version.']);
        $tbl->addColumn('quiz_id', 'integer', ['unsigned' => true, 'notnull' => true, 'comment' => 'The quiz this version belongs to.']);
        $tbl->addColumn('ruuid', 'string', ['notnull' => true, 'length' => 36, 'default' => '', 'comment' => 'Quiz revision uuid.']);
        $tbl->addColumn('uuid', 'string', ['notnull' => true, 'length' => 36, 'default' => '', 'comment' => 'Quiz uuid.']);
        $tbl->addColumn('title', 'string', ['length' => 255, 'notnull' => true, 'default' => '', 'comment' => 'The title of this version.']);
        $tbl->addColumn('description', 'text', ['notnull' => false, 'default' => null, 'comment' => 'The description of this version.']);
        $tbl->addColumn('status', 'boolean', ['default' => 1, 'comment' => 'Boolean indicating whether the quiz (at the time of this revision) is published.']);
        $tbl->addColumn('created', 'bigint', ['notnull' => true, 'default' => 0, 'comment' => 'A unix timestamp indicating when this version was created.']);
        $tbl->addColumn('pass_rate', 'smallint', ['unsigned' => true, 'notnull' => true, 'comment' => 'Passing rate out of 100.']);
        $tbl->addColumn('summary_pass', 'string', ['length' => 255, 'notnull' => false, 'comment' => 'Summary text for a passing grade.']);
        $tbl->addColumn('summary_default', 'string', ['length' => 255, 'notnull' => false, 'comment' => 'Summary text for any grade.']);
        $tbl->addColumn('randomization', 'boolean', ['default' => 0, 'comment' => 'Boolean field indicating if this quiz has random question order.']);
        $tbl->addColumn('backwards_navigation', 'boolean', ['default' => 1, 'comment' => 'Boolean indicating whether a quiz taker can navigate backwards.']);
        $tbl->addColumn('keep_results', 'smallint', ['unsigned' => true, 'notnull' => true, 'comment' => 'Enumerated field indicating if this quiz should prune old results. 0=only keep best, 1=only keep newest, 2=keep all']);
        $tbl->addColumn('repeat_until_correct', 'boolean', ['default' => 0, 'comment' => 'Boolean indicating whether a quiz taker must repeat the question until selecting the correct answer.']);
        $tbl->addColumn('feedback_time', 'smallint', ['unsigned' => true, 'default' => 2, 'comment' => 'Enumerated field indicating when to show feedback. 0=end of quiz, 1=end each question, 2=not show']);
        $tbl->addColumn('display_feedback', 'boolean', ['default' => 1]);
        $tbl->addColumn('quiz_open', 'datetimetz', ['notnull' => false, 'comment' => 'Datetime when the quiz open.']);
        $tbl->addColumn('quiz_close', 'datetimetz', ['notnull' => false, 'comment' => 'Datetime when the quiz close.']);
        $tbl->addColumn('takes', 'smallint', ['unsigned' => true, 'notnull' => true, 'default' => 0, 'comment' => 'Limit the number of times this quiz can be taken by a learner. 0 for unlimited.']);
        $tbl->addColumn('time_limit', 'integer', ['unsigned' => true, 'notnull' => false, 'comment' => 'Number of seconds for a user to complete an attempt.']);
        $tbl->addColumn('max_score', 'integer', ['notnull' => true, 'default' => 0, 'comment' => 'The max score of this quiz.']);
        $tbl->addColumn('allow_skipping', 'boolean', ['default' => 0, 'comment' => 'Boolean indicating whether the user can skip a question.']);
        $tbl->addColumn('allow_resume', 'boolean', ['default' => 1, 'comment' => 'Boolean indicating whether a user can resume a quiz after logging out and in.']);
        $tbl->addColumn('allow_jumping', 'boolean', ['default' => 0, 'comment' => 'Boolean indicating whether a user can skip to any question.']);
        $tbl->addColumn('taken', 'boolean', ['default' => 0, 'comment' => 'Boolean indicating whether the user can taken a quiz.']);
        $tbl->addColumn('is_redo_correct', 'boolean', ['default' => 1, 'comment' => 'Boolean indicating whether user has to redo correct answers.']);
        $tbl->addColumn('editor', 'integer', ['unsigned' => true, 'notnull' => true, 'comment' => 'Person who updated this quiz']);
        $tbl->addColumn('data', 'text', ['notnull' => false, 'default' => null, 'comment' => 'Json encoded extra data.']);
        $tbl->setPrimaryKey(['quiz_rid']);
        $tbl->addIndex(['quiz_id'], 'idx_quiz_revisions_quiz_id');
        $tbl->addIndex(['ruuid'], 'idx_quiz_revisions_ruuid');
        $tbl->addIndex(['uuid'], 'idx_quiz_revisions_uuid');

        // quiz_questions table
        $tbl = $schema->createTable('quiz_questions');
        $tbl->addColumn('relationship_id', 'integer', ['unsigned' => true, 'notnull' => true, 'autoincrement' => true, 'comment' => 'The primary identifier of this relationship.']);
        $tbl->addColumn('uuid', 'string', ['notnull' => true, 'length' => 36, 'default' => '', 'comment' => 'The universally unique identifier.']);
        $tbl->addColumn('quiz_uuid', 'string', ['notnull' => true, 'length' => 36, 'default' => '', 'comment' => 'The universally unique identifier.']);
        $tbl->addColumn('quiz_ruuid', 'string', ['notnull' => true, 'length' => 36, 'default' => '', 'comment' => 'Quiz revision uuid.']);
        $tbl->addColumn('question_uuid', 'string', ['notnull' => true, 'length' => 36, 'default' => '', 'comment' => 'The universally unique identifier.']);
        $tbl->addColumn('question_ruuid', 'string', ['notnull' => true, 'length' => 36, 'default' => '', 'comment' => 'Question revision uuid.']);
        $tbl->addColumn('weight', 'integer', ['notnull' => true, 'default' => 0, 'comment' => 'The weight of this question in the quiz.']);
        $tbl->addColumn('max_score', 'integer', ['notnull' => true, 'default' => 0, 'comment' => 'The max score of the question in this quiz.']);
        $tbl->addColumn('editor', 'integer', ['unsigned' => true, 'notnull' => true, 'comment' => 'Person who add this question in this quiz']);
        $tbl->addColumn('created', 'bigint', ['notnull' => true, 'default' => 0, 'comment' => 'The unix timestamp when relation was created.']);
        $tbl->addColumn('changed', 'bigint', ['notnull' => true, 'default' => 0, 'comment' => 'The unix timestamp when relation was updated.']);
        $tbl->addColumn('pass_rate', 'smallint', ['unsigned' => true, 'notnull' => false, 'default' => null, 'comment' => 'Passing rate out of 100.']);
        $tbl->setPrimaryKey(['relationship_id']);
        $tbl->addIndex(['uuid'], 'idx_quiz_questions_uuid');
        $tbl->addIndex(['quiz_uuid'], 'idx_quiz_questions_quiz_uuid');
        $tbl->addIndex(['quiz_ruuid'], 'idx_quiz_questions_quiz_ruuid');

        // result table
        $tbl = $schema->createTable('result');
        $tbl->addColumn('result_id', 'integer', ['unsigned' => true, 'notnull' => true, 'autoincrement' => true, 'comment' => 'The primary identifier for this result.']);
        $tbl->addColumn('uuid', 'string', ['notnull' => true, 'length' => 36, 'default' => '', 'comment' => 'The universally unique identifier.']);
        $tbl->addColumn('quiz_uuid', 'string', ['notnull' => true, 'length' => 36, 'default' => '', 'comment' => 'The universally unique identifier.']);
        $tbl->addColumn('quiz_ruuid', 'string', ['notnull' => true, 'length' => 36, 'default' => '', 'comment' => 'Quiz revision uuid.']);
        $tbl->addColumn('time_start', 'datetimetz', ['notnull' => false, 'comment' => 'Datetime when result starts.']);
        $tbl->addColumn('time_end', 'datetimetz', ['notnull' => false, 'comment' => 'Datetime when this result ends. A NULL value indicates a quiz result has not completed.']);
        $tbl->addColumn('score', 'integer', ['notnull' => true, 'default' => 0, 'comment' => 'The score of this result from 0 to 100.']);
        $tbl->addColumn('is_evaluated', 'boolean', ['default' => 0, 'comment' => 'Boolean indicating if this quiz requires manual grading and if it has been graded.']);
        $tbl->addColumn('is_best', 'boolean', ['notnull' => false, 'comment' => 'Is this best result per quiz_ruuid']);
        $tbl->addColumn('last_sequence', 'string', ['notnull' => false, 'length' => 36, 'comment' => 'The universally unique identifier.']);
        $tbl->addColumn('time_left', 'integer', ['notnull' => false]);
        $tbl->addColumn('outcome', 'string', ['length' => 255, 'notnull' => false, 'default' => null, 'comment' => 'Result outcome.']);
        $tbl->addColumn('taker', 'integer', ['unsigned' => true, 'notnull' => true, 'comment' => 'Person who takes this quiz']);
        $tbl->addColumn('data', 'text', ['notnull' => false, 'default' => null, 'comment' => 'Json encoded extra data.']);
        $tbl->addColumn('enrolment_id', 'integer', ['notnull' => false, 'default' => null, 'comment' => 'Learning item enrolment id']);
        $tbl->addColumn('counter', 'integer', ['unsigned' => true, 'notnull' => false, 'comment' => 'Number of attempts on a quiz revision']);
        $tbl->addColumn('cross_counter', 'integer', ['unsigned' => true, 'notnull' => false, 'comment' => 'Number of attempts on a quiz']);
        $tbl->setPrimaryKey(['result_id']);
        $tbl->addIndex(['uuid'], 'idx_result_uuid');
        $tbl->addIndex(['quiz_uuid', 'taker'], 'idx_result_quiz_uuid_taker');
        $tbl->addIndex(['quiz_ruuid', 'taker', 'is_best'], 'idx_result_quiz_ruuid_taker_is_best');
        $tbl->addIndex(['enrolment_id'], 'idx_result_enrolment_id');

        // sequence table
        $tbl = $schema->createTable('sequence');
        $tbl->addColumn('sequence_id', 'integer', ['unsigned' => true, 'notnull' => true, 'autoincrement' => true]);
        $tbl->addColumn('uuid', 'string', ['notnull' => true, 'length' => 36, 'default' => '', 'comment' => 'The universally unique identifier.']);
        $tbl->addColumn('delta', 'integer', ['unsigned' => true, 'notnull' => true, 'comment' => 'The sequential number of the sequence in this session.']);
        $tbl->addColumn('result_uuid', 'string', ['notnull' => true, 'length' => 36, 'default' => '', 'comment' => 'Result uuid.']);
        $tbl->addColumn('relation_uuid', 'string', ['notnull' => true, 'length' => 36, 'default' => '', 'comment' => 'Quiz-question relation uuid.']);
        $tbl->addColumn('quiz_ruuid', 'string', ['notnull' => true, 'length' => 36, 'default' => '', 'comment' => 'Quiz revision uuid.']);
        $tbl->addColumn('question_ruuid', 'string', ['notnull' => true, 'length' => 36, 'default' => '', 'comment' => 'Question revision uuid.']);
        $tbl->addColumn('prev_question_ruuid', 'string', ['notnull' => false, 'length' => 36, 'default' => '', 'comment' => 'Previous question revision uuid.']);
        $tbl->addColumn('answer_uuid', 'string', ['notnull' => false, 'length' => 36, 'default' => null, 'comment' => 'Answer uuid.']);
        $tbl->addColumn('is_backable', 'boolean', ['default' => 0, 'notnull' => false]);
        $tbl->addColumn('is_finishable', 'boolean', ['default' => 0, 'notnull' => false]);
        $tbl->addColumn('is_jumpable', 'boolean', ['default' => 0, 'notnull' => false]);
        $tbl->addColumn('is_answerable', 'boolean', ['default' => 0, 'notnull' => false]);
        $tbl->addColumn('is_skippable', 'boolean', ['default' => 0, 'notnull' => false]);
        $tbl->addColumn('next_sequence', 'string', ['notnull' => false, 'length' => 36]);
        $tbl->addColumn('prev_sequence', 'string', ['notnull' => false, 'length' => 36]);
        $tbl->addColumn('taker', 'integer', ['unsigned' => true, 'notnull' => true, 'comment' => 'Person who takes this quiz']);
        $tbl->addColumn('total', 'integer', ['unsigned' => true, 'notnull' => true, 'comment' => 'The total sequences in sequence (result).']);
        $tbl->addColumn('created', 'bigint', ['notnull' => true, 'default' => 0, 'comment' => 'The unix timestamp when sequence was created.']);
        $tbl->addColumn('data', 'text', ['notnull' => false, 'default' => null, 'comment' => 'Non query data.']);
        $tbl->setPrimaryKey(['sequence_id']);
        $tbl->addIndex(['uuid'], 'idx_sequence_uuid');
        $tbl->addIndex(['result_uuid'], 'idx_sequence_result_uuid');

        // correct sequence
        $tbl = $schema->createTable('sequence_correct');
        $tbl->addColumn('id', 'integer', ['unsigned' => true, 'notnull' => true, 'autoincrement' => true]);
        $tbl->addColumn('relation_uuid', 'string', ['notnull' => true, 'length' => 36, 'default' => '', 'comment' => 'Quiz-question relation uuid.']);
        $tbl->addColumn('answer_uuid', 'string', ['notnull' => false, 'length' => 36, 'default' => null, 'comment' => 'Answer uuid.']);
        $tbl->addColumn('quiz_ruuid', 'string', ['notnull' => true, 'length' => 36, 'default' => '', 'comment' => 'Quiz-question relation uuid.']);
        $tbl->addColumn('taker', 'integer', ['unsigned' => true, 'notnull' => true, 'comment' => 'Person who takes this quiz']);
        $tbl->setPrimaryKey(['id']);
        $tbl->addIndex(['quiz_ruuid', 'taker'], 'idx_sequence_correct_quiz_ruuid_taker');

        // answer table
        $tbl = $schema->createTable('answer');
        $tbl->addColumn('answer_id', 'integer', ['unsigned' => true, 'notnull' => true, 'autoincrement' => true, 'comment' => 'The primary identifier for this result answer.']);
        $tbl->addColumn('uuid', 'string', ['notnull' => true, 'length' => 36, 'default' => '', 'comment' => 'The universally unique identifier.']);
        $tbl->addColumn('question_uuid', 'string', ['notnull' => true, 'length' => 36, 'default' => '', 'comment' => 'Question uuid.']);
        $tbl->addColumn('question_ruuid', 'string', ['notnull' => true, 'length' => 36, 'default' => '', 'comment' => 'Question revision uuid.']);
        $tbl->addColumn('question_type', 'string', ['length' => 32, 'notnull' => true, 'default' => '', 'comment' => 'Type of the question that being answered.']);
        $tbl->addColumn('is_correct', 'boolean', ['default' => 0, 'comment' => 'Boolean indicating whether this answer was correct.']);
        $tbl->addColumn('is_skipped', 'boolean', ['default' => 0, 'comment' => 'Boolean indicating whether this question was skipped.']);
        $tbl->addColumn('is_evaluated', 'boolean', ['default' => 0, 'comment' => 'Boolean indicating whether this question was evaluated.']);
        $tbl->addColumn('points', 'float', ['notnull' => false, 'default' => null, 'comment' => 'Scaled points awarded for this response.']);
        $tbl->addColumn('answer_timestamp', 'bigint', ['unsigned' => true, 'comment' => 'Unix timestamp when this question was answered.']);
        $tbl->addColumn('changed', 'bigint', ['unsigned' => true, 'comment' => 'Unix timestamp when this question was re-answered.']);
        $tbl->addColumn('taker', 'integer', ['unsigned' => true, 'notnull' => true, 'comment' => 'Person who answered this question']);
        $tbl->addColumn('answer', 'text', ['notnull' => false, 'default' => null, 'comment' => 'Json encoded data.']);
        $tbl->setPrimaryKey(['answer_id']);
        $tbl->addIndex(['uuid'], 'idx_answer_uuid');
        $tbl->addIndex(['question_ruuid'], 'idx_answer_question_ruuid');

        // person
        $tbl = $schema->createTable('person');
        $tbl->addColumn('person_id', 'integer', ['unsigned' => true, 'notnull' => true, 'autoincrement' => true, 'comment' => 'Internal primary identifier for person.']);
        $tbl->addColumn('external_source', 'string', ['length' => 255, 'notnull' => true, 'default' => '', 'comment' => 'The external source.']);
        $tbl->addColumn('external_identifier', 'string', ['length' => 255, 'notnull' => true, 'default' => '', 'comment' => 'The external identifier.']);
        $tbl->addColumn('secondary_identifier', 'string', ['length' => 255, 'notnull' => false, 'default' => null, 'comment' => 'Secondary identifier.']);
        $tbl->addColumn('mail', 'string', ['length' => 255, 'notnull' => false, 'default' => null, 'comment' => 'Email']);
        $tbl->addColumn('created', 'bigint', ['notnull' => true, 'default' => 0, 'comment' => 'The unix timestamp when this person was added.']);
        $tbl->addColumn('custom', 'text', ['notnull' => false, 'default' => null, 'comment' => 'Json encoded data.']);
        $tbl->setPrimaryKey(['person_id']);
        $tbl->addIndex(['external_source', 'external_identifier'], 'idx_person_external_source_external_identifier');

        // question table
        $tbl = $schema->createTable('question');
        $tbl->addColumn('question_id', 'integer', ['unsigned' => true, 'notnull' => true, 'autoincrement' => true, 'comment' => 'The primary identifier of a question.']);
        $tbl->addColumn('question_rid', 'integer', ['unsigned' => true, 'notnull' => false, 'default' => null, 'comment' => 'The current question_revisions.question_rid version identifier.']);
        $tbl->addColumn('question_type', 'string', ['length' => 32, 'notnull' => true, 'default' => '', 'comment' => 'The question type of this question.']);
        $tbl->addColumn('uuid', 'string', ['notnull' => true, 'length' => 36, 'default' => '', 'comment' => 'The universally unique identifier.']);
        $tbl->addColumn('ruuid', 'string', ['notnull' => false, 'length' => 36, 'default' => '', 'comment' => 'Question revision uuid.']);
        $tbl->addColumn('title', 'string', ['length' => 255, 'notnull' => true, 'default' => '', 'comment' => 'The title of this question.']);
        $tbl->addColumn('description', 'text', ['notnull' => false, 'default' => null, 'comment' => 'The description of this question.']);
        $tbl->addColumn('feedback', 'text', ['notnull' => false, 'default' => null, 'comment' => 'The generic feedback text to show for this question.']);
        $tbl->addColumn('status', 'boolean', ['default' => 1, 'comment' => 'Boolean indicating whether the question is published.']);
        $tbl->addColumn('created', 'bigint', ['notnull' => true, 'default' => 0, 'comment' => 'The unix timestamp when the question was created.']);
        $tbl->addColumn('changed', 'bigint', ['notnull' => true, 'default' => 0, 'comment' => 'The unix timestamp when the question was most recently saved.']);
        $tbl->addColumn('editor', 'integer', ['unsigned' => true, 'notnull' => true, 'comment' => 'Person who created this quiz']);
        $tbl->addColumn('data', 'text', ['notnull' => false, 'comment' => 'Json encoded extra data.']);
        $tbl->addColumn('config', 'text', ['notnull' => false, 'default' => null, 'comment' => 'Json encoded config.']);
        $tbl->addColumn('li_id', 'integer', ['notnull' => false, 'default' => null, 'comment' => 'Learning item id']);
        $tbl->setPrimaryKey(['question_id']);
        $tbl->addUniqueIndex(['question_rid'], 'unq_question_question_rid');
        $tbl->addIndex(['uuid'], 'idx_question_uuid');
        $tbl->addIndex(['li_id', 'uuid'], 'idx_question_li_id_uuid');

        // question result
        $tbl = $schema->createTable('question_result');
        $tbl->addColumn('result_id', 'integer', ['unsigned' => true, 'notnull' => true, 'autoincrement' => true, 'comment' => 'The primary identifier for this result.']);
        $tbl->addColumn('uuid', 'string', ['notnull' => true, 'length' => 36, 'default' => '', 'comment' => 'The universally unique identifier.']);
        $tbl->addColumn('question_uuid', 'string', ['notnull' => true, 'length' => 36, 'default' => '', 'comment' => 'Question uuid.']);
        $tbl->addColumn('question_ruuid', 'string', ['notnull' => true, 'length' => 36, 'default' => '', 'comment' => 'Question revision uuid.']);
        $tbl->addColumn('answer_uuid', 'string', ['notnull' => false, 'length' => 36, 'default' => null, 'comment' => 'Answer uuid.']);
        $tbl->addColumn('time_start', 'datetimetz', ['notnull' => false, 'comment' => 'Datetime when this result starts.']);
        $tbl->addColumn('time_end', 'datetimetz', ['notnull' => false, 'comment' => 'Datetime when this result ends. A NULL value indicates a quiz result not completed.']);
        $tbl->addColumn('taker', 'integer', ['unsigned' => true, 'notnull' => true, 'comment' => 'Person who takes this quiz']);
        $tbl->setPrimaryKey(['result_id']);
        $tbl->addIndex(['uuid'], 'idx_question_result_uuid');
        $tbl->addIndex(['question_ruuid', 'taker'], 'idx_question_result_question_ruuid_taker');

        // question_revisions table
        $tbl = $schema->createTable('question_revisions');
        $tbl->addColumn('question_rid', 'integer', ['unsigned' => true, 'notnull' => true, 'autoincrement' => true, 'comment' => 'The primary identifier for this revision.']);
        $tbl->addColumn('question_id', 'integer', ['unsigned' => true, 'notnull' => false, 'default' => null, 'comment' => 'The question this revision belongs to.']);
        $tbl->addColumn('question_type', 'string', ['length' => 32, 'notnull' => true, 'default' => '', 'comment' => 'The question type of this question.']);
        $tbl->addColumn('ruuid', 'string', ['notnull' => true, 'length' => 36, 'default' => '', 'comment' => 'Question revision uuid.']);
        $tbl->addColumn('uuid', 'string', ['notnull' => true, 'length' => 36, 'default' => '', 'comment' => 'Question uuid.']);
        $tbl->addColumn('title', 'string', ['length' => 255, 'notnull' => true, 'default' => '', 'comment' => 'The title of this revision.']);
        $tbl->addColumn('description', 'text', ['notnull' => false, 'default' => null, 'comment' => 'The description of this revision.']);
        $tbl->addColumn('feedback', 'text', ['notnull' => false, 'default' => null, 'comment' => 'The generic feedback text to show of this revision.']);
        $tbl->addColumn('status', 'boolean', ['default' => 1, 'comment' => 'Boolean indicating whether the question (at the time of this revision) is published.']);
        $tbl->addColumn('created', 'bigint', ['notnull' => true, 'default' => 0, 'comment' => 'A unix timestamp indicating when this revision was created.']);
        $tbl->addColumn('editor', 'integer', ['unsigned' => true, 'notnull' => true, 'comment' => 'Person who updated this question']);
        $tbl->addColumn('data', 'text', ['notnull' => false, 'comment' => 'Json encoded extra data.']);
        $tbl->addColumn('config', 'text', ['notnull' => false, 'default' => null, 'comment' => 'Json encoded config.']);
        $tbl->addColumn('li_id', 'integer', ['notnull' => false, 'default' => null, 'comment' => 'Learning item id']);
        $tbl->setPrimaryKey(['question_rid']);
        $tbl->addIndex(['ruuid'], 'idx_question_revisions_ruuid');
        $tbl->addIndex(['uuid'], 'idx_question_revisions_uuid');

        $tbl = $schema->createTable('indexing');
        $tbl->addColumn('indexing_id', 'integer', ['unsigned' => true, 'notnull' => true, 'autoincrement' => true, 'comment' => 'The primary identifier for this indexing.']);
        $tbl->addColumn('uuid', 'string', ['notnull' => true, 'length' => 36, 'default' => '', 'comment' => 'The universally unique identifier.']);
        $tbl->addColumn('indexing_type', 'string', ['length' => 255, 'notnull' => true, 'default' => '', 'comment' => 'Type of this indexing.']);
        $tbl->addColumn('stopped', 'boolean', ['default' => 1, 'comment' => 'Boolean indicating whether the indexing is stopped.']);
        $tbl->addColumn('data', 'text', ['notnull' => false, 'default' => null, 'comment' => 'Non query data.']);
        $tbl->setPrimaryKey(['indexing_id']);
        $tbl->addIndex(['uuid'], 'idx_indexing_uuid');
        $tbl->addIndex(['indexing_type', 'stopped'], 'idx_indexing_indexing_type_stopped');

        $tbl = $schema->createTable('task');
        $tbl->addColumn('task_id', 'integer', ['unsigned' => true, 'notnull' => true, 'autoincrement' => true, 'comment' => 'Primary identifier of a task']);
        $tbl->addColumn('name', 'string', ['length' => 255, 'notnull' => true, 'default' => '', 'comment' => 'Task name']);
        $tbl->addColumn('uuid', 'string', ['notnull' => true, 'length' => 36, 'default' => '', 'comment' => 'Universally unique identifier']);
        $tbl->addColumn('status', 'string', ['default' => 'unprocessed', 'length' => 255, 'notnull' => true]);
        $tbl->addColumn('created', 'bigint', ['notnull' => true, 'default' => 0, 'comment' => 'The unix timestamp when task was created']);
        $tbl->addColumn('changed', 'bigint', ['notnull' => true, 'default' => 0, 'comment' => 'The unix timestamp when task was updated']);
        $tbl->addColumn('processes', 'smallint', ['comment' => 'Number of jobs']);
        $tbl->addColumn('processed', 'smallint', ['default' => 0, 'comment' => 'Number of successful jobs']);
        $tbl->addColumn('result', 'text', ['notnull' => false, 'comment' => 'Processed result']);
        $tbl->addColumn('data', 'text', ['notnull' => false, 'comment' => 'Shared data for job']);
        $tbl->setPrimaryKey(['task_id']);
        $tbl->addIndex(['uuid'], 'idx_task_uuid');
    }
}
