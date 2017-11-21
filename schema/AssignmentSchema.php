<?php

namespace go1\util\schema;

use Doctrine\DBAL\Schema\Schema;

class AssignmentSchema
{
    public static function install(Schema $schema)
    {
        if (!$schema->hasTable('asm_assignment')) {
            $assignment = $schema->createTable('asm_assignment');
            $assignment->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
            $assignment->addColumn('user_id', 'integer', ['unsigned' => true]);
            $assignment->addColumn('course_id', 'integer', ['unsigned' => true]);
            $assignment->addColumn('module_id', 'integer', ['unsigned' => true]);
            $assignment->addColumn('created', 'integer', ['unsigned' => true]);
            $assignment->addColumn('updated', 'integer', ['unsigned' => true]);
            $assignment->addColumn('published', 'smallint');
            $assignment->addColumn('title', 'string');
            $assignment->addColumn('description', 'text', ['notnull' => false]);
            $assignment->addColumn('data', 'blob');
            $assignment->setPrimaryKey(['id']);
            $assignment->addIndex(['user_id']);
            $assignment->addIndex(['course_id']);
            $assignment->addIndex(['module_id']);
            $assignment->addIndex(['created']);
            $assignment->addIndex(['updated']);
            $assignment->addIndex(['title']);
            $assignment->addIndex(['published']);
        }

        if (!$schema->hasTable('asm_submission')) {
            $submission = $schema->createTable('asm_submission');
            $submission->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
            $submission->addColumn('revision_id', 'integer', ['unsigned' => true, 'notnull' => false]);
            $submission->addColumn('assignment_id', 'integer', ['unsigned' => true]);
            $submission->addColumn('profile_id', 'integer', ['unsigned' => true]);
            $submission->addColumn('status', 'smallint');
            $submission->addColumn('created', 'integer', ['unsigned' => true]);
            $submission->addColumn('updated', 'integer', ['unsigned' => true]);
            $submission->addColumn('published', 'smallint', ['default' => 0]);
            $submission->addColumn('enrolment_id', 'integer', ['unsigned' => true, 'default' => 0]);
            $submission->addColumn('archived', 'smallint', ['default' => 0]);
            $submission->setPrimaryKey(['id']);
            $submission->addIndex(['assignment_id']);
            $submission->addIndex(['profile_id']);
            $submission->addIndex(['created']);
            $submission->addIndex(['updated']);
            $submission->addIndex(['published']);
            $submission->addIndex(['status']);
            $submission->addForeignKeyConstraint('asm_assignment', ['assignment_id'], ['id']);
            $submission->addForeignKeyConstraint('asm_submission_revision', ['revision_id'], ['id'], ['onDelete' => 'SET NULL']);
            $submission->addUniqueIndex(['assignment_id', 'profile_id', 'enrolment_id']);
        }

        if (!$schema->hasTable('asm_submission_revision')) {
            $revision = $schema->createTable('asm_submission_revision');
            $revision->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
            $revision->addColumn('submission_id', 'integer', ['unsigned' => true]);
            $revision->addColumn('actor_id', 'integer', ['unsigned' => true, 'not_null' => false, 'description' => 'Person mark assignment submission for users.']);
            $revision->addColumn('created', 'integer', ['unsigned' => true]);
            $revision->addColumn('updated', 'integer', ['unsigned' => true]);
            $revision->addColumn('status', 'smallint', ['notnull' => false]);
            $revision->addColumn('data', 'blob');
            $revision->setPrimaryKey(['id']);
            $revision->addIndex(['created']);
            $revision->addIndex(['status']);
        }

        if (!$schema->hasTable('asm_feedback')) {
            $feedback = $schema->createTable('asm_feedback');
            $feedback->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
            $feedback->addColumn('assessor_id', 'integer', ['unsigned' => true]);
            $feedback->addColumn('submission_rid', 'integer', ['unsigned' => true]);
            $feedback->addColumn('published', 'smallint');
            $feedback->addColumn('status', 'smallint');
            $feedback->addColumn('data', 'blob');
            $feedback->addColumn('created', 'integer', ['unsigned' => true]);
            $feedback->addColumn('updated', 'integer', ['unsigned' => true]);
            $feedback->setPrimaryKey(['id']);
            $feedback->addIndex(['assessor_id']);
            $feedback->addIndex(['submission_rid']);
            $feedback->addIndex(['published']);
            $feedback->addIndex(['status']);
            $feedback->addIndex(['created']);
            $feedback->addIndex(['updated']);
        }

        if (!$schema->hasTable('asm_import')) {
            $import = $schema->createTable('asm_import');
            $import->addColumn('type', 'string');
            $import->addColumn('id', 'integer');
            $import->addColumn('remote_id', 'integer', ['unsigned' => true]);
            $import->addColumn('created', 'integer', ['unsigned' => true]);
            $import->addColumn('updated', 'integer', ['unsigned' => true]);
            $import->setPrimaryKey(['type', 'id', 'remote_id']);
            $import->addIndex(['created']);
            $import->addIndex(['updated']);
        }
    }
}
