<?php

namespace go1\util\es\mock;

use Elasticsearch\Client;
use go1\util\DateTime;
use go1\util\es\Schema;

trait EsAssignmentMockTrait
{
    public function createEsSubmission(Client $client, $options = [])
    {
        static $autoId;

        $submission = [
            'id'          => $options['id'] ?? ++$autoId,
            'revision_id' => $options['revision_id'] ?? 0,
            'profile_id'  => $options['profile_id'] ?? 0,
            'status'      => $options['status'] ?? 0,
            'published'   => $options['published'] ?? 0,
            'created'     => DateTime::formatDate($options['created'] ?? time()),
            'updated'     => DateTime::formatDate($options['updated'] ?? time()),
            'assessors'   => $options['assessors'] ?? [],
        ];

        return $client->create([
            'index'   => Schema::INDEX,
            'routing' => Schema::INDEX,
            'type'    => Schema::O_SUBMISSION,
            'id'      => $submission['id'],
            'body'    => $submission,
            'parent'  => $options['enrolment_id'] ?? 0,
            'refresh' => true,
        ]);
    }

    public function createEsSubmissionRevision(Client $client, $options = [])
    {
        static $autoId;

        isset($options['data']) && is_scalar($options['data']) && $options['data'] = json_decode($options['data'], true);

        $revision = [
            'id'      => $options['id'] ?? ++$autoId,
            'status'  => $options['status'] ?? 0,
            'created' => DateTime::formatDate($options['created'] ?? time()),
            'updated' => DateTime::formatDate($options['updated'] ?? time()),
            'data'    => $options['data'] ?? [],
        ];

        return $client->create([
            'index'   => Schema::INDEX,
            'routing' => Schema::INDEX,
            'type'    => Schema::O_SUBMISSION_REVISION,
            'id'      => $revision['id'],
            'body'    => $revision,
            'parent'  => $options['submission_id'] ?? null,
            'refresh' => true,
        ]);
    }
}
