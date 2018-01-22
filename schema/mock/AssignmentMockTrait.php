<?php

namespace go1\util\schema\mock;

use Doctrine\DBAL\Connection;

trait AssignmentMockTrait
{
    public function createAssignment(Connection $db, array $options = [])
    {
        if (isset($options['data']) && !is_scalar($options['data'])) {
            $options['data'] = json_encode($options['data']);
        }

        $db->insert('asm_assignment', [
            'id'            => isset($options['id']) ? $options['id'] : null,
            'user_id'       => isset($options['user_id']) ? $options['user_id'] : 1,
            'course_id'     => isset($options['course_id']) ? $options['course_id'] : 1,
            'module_id'     => isset($options['module_id']) ? $options['module_id'] : 1,
            'created'       => isset($options['created']) ? $options['created'] : time(),
            'updated'       => isset($options['updated']) ? $options['updated'] : time(),
            'published'     => isset($options['published']) ? $options['published'] : 1,
            'title'         => isset($options['title']) ? $options['title'] : 'Foo assignment',
            'description'   => isset($options['description']) ? $options['description'] : 'Kitty is awesome',
            'data'          => isset($options['data']) ? $options['data'] : '',
        ]);

        return $db->lastInsertId('asm_assignment');
    }

    public function createSubmission(Connection $db, array $options = [])
    {
        $db->insert('asm_submission', [
            'id'            => isset($options['id']) ? $options['id'] : null,
            'assignment_id' => isset($options['assignment_id']) ? $options['assignment_id'] : 1,
            'revision_id'   => isset($options['revision_id']) ? $options['revision_id'] : 1,
            'profile_id'    => isset($options['profile_id']) ? $options['profile_id'] : 1,
            'enrolment_id'   => isset($options['enrolment_id']) ? $options['enrolment_id'] : 1,
            'status'        => isset($options['status']) ? $options['status'] : 1,
            'created'       => isset($options['created']) ? $options['created'] : time(),
            'updated'       => isset($options['updated']) ? $options['updated'] : time(),
            'published'     => isset($options['published']) ? $options['published'] : 1,
        ]);

        return $db->lastInsertId('asm_submission');
    }

    public function createSubmissionRevision(Connection $db, array $options = [])
    {
        if (isset($options['data']) && !is_scalar($options['data'])) {
            $options['data'] = json_encode($options['data']);
        }

        $db->insert('asm_submission_revision', [
            'id'            => isset($options['id']) ? $options['id'] : null,
            'submission_id' => isset($options['submission_id']) ? $options['submission_id'] : 1,
            'actor_id'      => isset($options['actor_id']) ? $options['actor_id'] : 1,
            'created'       => isset($options['created']) ? $options['created'] : time(),
            'updated'       => isset($options['updated']) ? $options['updated'] : time(),
            'status'        => isset($options['status']) ? $options['status'] : 1,
            'data'          => isset($options['data']) ? $options['data'] : '',
        ]);

        return $db->lastInsertId('asm_submission_revision');
    }
}
