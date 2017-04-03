<?php

namespace go1\util\es\mock;

use Elasticsearch\Client;
use go1\util\es\Schema;
use go1\util\DateTime;

trait EsAssignmentMockTrait
{
    public function createEsAssignment(Client $client, $options = [])
    {
        static $autoId;

        isset($options['data']) && is_scalar($options['data']) && $options['data'] = json_decode($options['data'], true);

        $asm = [
            'id'          => $options['id'] ?? ++$autoId,
            'user_id'     => $options['user_id'] ?? 0,
            'module_id'   => $options['module_id'] ?? 0,
            'created'     => DateTime::formatDate($options['created'] ?? time()),
            'updated'     => DateTime::formatDate($options['updated'] ?? time()),
            'published'   => $options['published'] ?? 0,
            'title'       => $options['title'] ?? 'Foo assignment',
            'description' => $options['description'] ?? '',
            'data'        => $options['data'] ?? [],
        ];

        return $client->create([
            'index'   => Schema::INDEX,
            'routing' => Schema::INDEX,
            'type'    => Schema::O_ASSIGNMENT,
            'id'      => $asm['id'],
            'body'    => $asm,
            'parent'  => $options['course_id'] ?? null,
            'refresh' => true
        ]);
    }

    public function createEsSubmission(Client $client, $options = [])
    {
        static $autoId;

        $submission = [
            'id'            => $options['id'] ?? ++$autoId,
            'revision_id'   => $options['revision_id'] ?? 0,
            'profile_id'    => $options['profile_id'] ?? 0,
            'status'        => $options['status'] ?? 0,
            'published'     => $options['published'] ?? 0,
            'created'       => DateTime::formatDate($options['created'] ?? time()),
            'updated'       => DateTime::formatDate($options['updated'] ?? time()),
            'assessors'     => $options['assessors'] ?? [],
        ];

        return $client->create([
            'index'   => Schema::INDEX,
            'routing' => Schema::INDEX,
            'type'    => Schema::O_SUBMISSION,
            'id'      => $submission['id'],
            'body'    => $submission,
            'parent'  => $options['assignment_id'] ?? null,
            'refresh' => true
        ]);
    }

    public function createEsSubmissionRevision(Client $client, $options = [])
    {
        static $autoId;

        isset($options['data']) && is_scalar($options['data']) && $options['data'] = json_decode($options['data'], true);

        $revision = [
            'id'            => $options['id'] ?? ++$autoId,
            'status'        => $options['status'] ?? 0,
            'created'       => DateTime::formatDate($options['created'] ?? time()),
            'updated'       => DateTime::formatDate($options['updated'] ?? time()),
            'data'          => $options['data'] ?? [],
        ];

        return $client->create([
            'index'   => Schema::INDEX,
            'routing' => Schema::INDEX,
            'type'    => Schema::O_SUBMISSION_REVISION,
            'id'      => $revision['id'],
            'body'    => $revision,
            'parent'  => null,
            'refresh' => true
        ]);
    }
}
