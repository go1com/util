<?php

namespace go1\util\es\mock;

use Elasticsearch\Client;
use go1\util\es\Schema;
use go1\util\DateTime;

trait EsEnrolmentMockTrait
{
    public function createEsEnrolment(Client $client, $options = [])
    {
        static $autoId;

        $enrolment = [
            'id'         => $options['id'] ?? ++$autoId,
            'profile_id' => $options['profile_id'] ?? 0,
            'lo_id'      => $options['lo_id'] ?? 0,
            'parent_id'  => $options['parent_id'] ?? 0,
            'status'     => $options['status'] ?? 0,
            'result'     => $options['result'] ?? 0,
            'pass'       => $options['pass'] ?? false,
            'assessors'  => $options['assessors'] ?? [],
            'start_date' => DateTime::formatDate($options['start_date'] ?? time()),
            'end_date'   => isset($options['end_date']) ? DateTime::formatDate($options['end_date']) : null,
            'changed'    => DateTime::formatDate($options['changed'] ?? time()),
        ];

        return $client->create([
            'index'   => Schema::INDEX,
            'routing' => Schema::INDEX,
            'type'    => Schema::O_ENROLMENT,
            'id'      => $enrolment['id'],
            'body'    => $enrolment,
            'parent'  => $options['lo_id'] ?? 0,
            'refresh' => true
        ]);
    }
}
