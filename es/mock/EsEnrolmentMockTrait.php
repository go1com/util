<?php

namespace go1\util\es\mock;

use Elasticsearch\Client;
use go1\util\DateTime;
use go1\util\enrolment\EnrolmentTypes;
use go1\util\es\Schema;

trait EsEnrolmentMockTrait
{
    public function createEsEnrolment(Client $client, $options = [])
    {
        static $autoId;

        $enrolment = [
            'id'            => $options['id'] ?? ++$autoId,
            'type'          => $options['type'] ?? EnrolmentTypes::TYPE_ENROLMENT,
            'profile_id'    => $options['profile_id'] ?? 0,
            'lo_id'         => $options['lo_id'] ?? 0,
            'parent_id'     => $options['parent_id'] ?? 0,
            'status'        => $options['status'] ?? 0,
            'last_status'   => $options['last_status'] ?? $options['status'] ?? 0,
            'result'        => $options['result'] ?? 0,
            'pass'          => $options['pass'] ?? 0,
            'assessors'     => $options['assessors'] ?? [],
            'assigned_date' => isset($options['assigned_date']) ? DateTime::formatDate($options['assigned_date']) : null,
            'start_date'    => DateTime::formatDate($options['start_date'] ?? time()),
            'end_date'      => isset($options['end_date']) ? DateTime::formatDate($options['end_date']) : null,
            'due_date'      => isset($options['due_date']) ? DateTime::formatDate($options['due_date']) : null,
            'expire_date'   => isset($options['expire_date']) ? DateTime::formatDate($options['expire_date']) : null,
            'begin_expire'  => isset($options['begin_expire']) ? DateTime::formatDate($options['begin_expire']) : null,
            'changed'       => DateTime::formatDate($options['changed'] ?? time()),
            'lo'            => $options['lo'] ?? null,
            'account'       => $options['account'] ?? null,
            'progress'      => $options['progress'] ?? [],
            'is_assigned'   => $options['is_assigned'] ?? 0,
            'quantity'      => $options['quantity'] ?? 0,
            'certificate'   => [
                'url'  => $options['certificate']['url'] ?? null,
                'name' => $options['certificate']['name'] ?? null,
                'size' => $options['certificate']['size'] ?? null,
            ],
            'metadata'      => [
                'account_id'          => $options['account_id'] ?? 0,
                'course_enrolment_id' => $options['metadata']['course_enrolment_id'] ?? 0,
                'course_id'           => $options['metadata']['course_id'] ?? 0,
                'status'              => $options['metadata']['status'] ?? 0,
                'has_assessor'        => $options['metadata']['has_assessor'] ?? 0,
                'user_id'             => $options['metadata']['user_id'] ?? 0,
                'instance_id'         => $options['instance_id'] ?? 0,
                'updated_at'          => $options['updated_at'] ?? time(),
            ],
        ];

        return $client->create([
            'index'   => $options['index'] ?? Schema::INDEX,
            'routing' => $options['routing'] ?? Schema::INDEX,
            'type'    => Schema::O_ENROLMENT,
            'id'      => $enrolment['id'],
            'parent'  => $options['parent'] ?? $enrolment['lo_id'],
            'body'    => $enrolment,
            'refresh' => true,
        ]);
    }

    public function createEsRevisionEnrolment(Client $client, $options = [])
    {
        static $autoId;

        $enrolmentRevision = [
            'id'                  => $options['id'] ?? ++$autoId,
            'user_id'             => $options['user_id'] ?? 0,
            'portal_id'           => $options['portal_id'] ?? 0,
            'lo_id'               => $options['lo_id'] ?? 0,
            'parent_lo_id'        => $options['parent_lo_id'] ?? null,
            'enrolment_id'        => $options['enrolment_id'] ?? 0,
            'parent_enrolment_id' => $options['parent_enrolment_id'] ?? null,
            'start_date'          => DateTime::formatDate($options['start_date'] ?? time()),
            'end_date'            => isset($options['end_date']) ? DateTime::formatDate($options['end_date']) : null,
            'status'              => $options['status'] ?? 0,
            'result'              => $options['result'] ?? 0,
            'pass'                => $options['pass'] ?? 0,
            'progress'            => $options['progress'] ?? [],
            'timestamp'           => DateTime::formatDate($options['timestamp'] ?? time()),
            'metadata'            => [
                'instance_id' => $options['instance_id'] ?? 0,
            ],
        ];

        return $client->create([
            'index'   => $options['index'] ?? Schema::INDEX,
            'routing' => $options['routing'] ?? $options['instance_id'],
            'type'    => Schema::O_ENROLMENT_REVISION,
            'id'      => $enrolmentRevision['id'],
            'parent'  => $options['parent'] ?? $enrolmentRevision['lo_id'],
            'body'    => $enrolmentRevision,
            'refresh' => true,
        ]);
    }
}
