<?php

namespace go1\util\schema\mock;

use DateTime;
use Doctrine\DBAL\Connection;

trait EnrolmentMockTrait
{
    public function createEnrolment(Connection $db, array $options = [])
    {
        $profileId = isset($options['profile_id']) ? $options['profile_id'] : 0;

        $db->insert('gc_enrolment', [
            'profile_id'        => $profileId,
            'parent_lo_id'      => isset($options['parent_lo_id']) ? $options['parent_lo_id'] : 0,
            'lo_id'             => isset($options['lo_id']) ? $options['lo_id'] : 0,
            'instance_id'       => isset($options['instance_id']) ? $options['instance_id'] : 0,
            'taken_instance_id' => isset($options['taken_instance_id']) ? $options['taken_instance_id'] : 0,
            'start_date'        => isset($options['start_date']) ? $options['start_date'] : (new DateTime)->format('Y-m-d h:i:s'),
            'end_date'          => isset($options['end_date']) ? $options['end_date'] : null,
            'status'            => isset($options['status']) ? $options['status'] : 'in-progress',
            'result'            => isset($options['result']) ? $options['result'] : 0,
            'pass'              => isset($options['pass']) ? $options['pass'] : 0,
            'timestamp'         => isset($options['timestamp']) ? $options['timestamp'] : time(),
            'changed'           => isset($options['changed']) ? $options['changed'] : time(),
            'data'              => isset($options['data']) ? $options['data'] : '',
        ]);

        return $db->lastInsertId('gc_enrolment');
    }
}
