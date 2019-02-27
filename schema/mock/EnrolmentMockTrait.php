<?php

namespace go1\util\schema\mock;

use DateTime;
use Doctrine\DBAL\Connection;
use go1\util\enrolment\EnrolmentStatuses;
use go1\util\lo\LoHelper;

trait EnrolmentMockTrait
{
    public function createEnrolment(Connection $db, array $options = [])
    {
        $profileId = isset($options['profile_id']) ? $options['profile_id'] : 0;

        $db->insert('gc_enrolment', [
            'id'                => $options['id'] ?? null,
            'profile_id'        => $profileId,
            'parent_lo_id'      => isset($options['parent_lo_id']) ? $options['parent_lo_id'] : 0,
            'parent_enrolment_id' => isset($options['parent_enrolment_id']) ? $options['parent_enrolment_id'] : 0,
            'lo_id'             => isset($options['lo_id']) ? $options['lo_id'] : 0,
            'instance_id'       => isset($options['instance_id']) ? $options['instance_id'] : 0,
            'taken_instance_id' => isset($options['taken_instance_id']) ? $options['taken_instance_id'] : 0,
            'start_date'        => isset($options['start_date']) ? $options['start_date'] : (new DateTime)->format(DATE_ISO8601),
            'end_date'          => isset($options['end_date']) ? $options['end_date'] : null,
            'status'            => isset($options['status']) ? $options['status'] : EnrolmentStatuses::IN_PROGRESS,
            'result'            => isset($options['result']) ? $options['result'] : 0,
            'pass'              => isset($options['pass']) ? $options['pass'] : 0,
            'timestamp'         => isset($options['timestamp']) ? $options['timestamp'] : time(),
            'changed'           => isset($options['changed']) ? $options['changed'] : time(),
            'data'              => isset($options['data']) ? (is_scalar($options['data']) ? $options['data'] : json_encode($options['data'])) : '',
        ]);

        $id = $options['id'] ?? $db->lastInsertId('gc_enrolment');
        $id && !empty($options['lo_id']) && $this->updateLoEnrolmentCountCache($db, $options['lo_id']);

        return $id;
    }

    private function updateLoEnrolmentCountCache(Connection $db, int $loId)
    {
        $lo = LoHelper::load($db, $loId);
        if ($lo) {
            $db->update(
                'gc_lo',
                [
                    'enrolment_count' => (int) $lo->enrolment_count + 1,
                ],
                [
                    'id' => $loId,
                ]
            );
        }
    }

    public function createRevisionEnrolment(Connection $db, array $options = [])
    {
        $profileId = isset($options['profile_id']) ? $options['profile_id'] : 0;

        $db->insert('gc_enrolment_revision', [
            'id'                  => $options['id'] ?? null,
            'profile_id'          => $profileId,
            'lo_id'               => isset($options['lo_id']) ? $options['lo_id'] : 0,
            'instance_id'         => isset($options['instance_id']) ? $options['instance_id'] : 0,
            'taken_instance_id'   => isset($options['taken_instance_id']) ? $options['taken_instance_id'] : 0,
            'start_date'          => isset($options['start_date']) ? $options['start_date'] : (new DateTime)->format('Y-m-d h:i:s'),
            'end_date'            => isset($options['end_date']) ? $options['end_date'] : null,
            'status'              => isset($options['status']) ? $options['status'] : EnrolmentStatuses::IN_PROGRESS,
            'result'              => isset($options['result']) ? $options['result'] : 0,
            'pass'                => isset($options['pass']) ? $options['pass'] : 0,
            'parent_lo_id'        => isset($options['parent_lo_id']) ? $options['parent_lo_id'] : 0,
            'enrolment_id'        => isset($options['enrolment_id']) ? $options['enrolment_id'] : 0,
            'note'                => isset($options['note']) ? $options['note'] : '',
            'data'                => isset($options['data']) ? $options['data'] : '',
            'parent_enrolment_id' => $options['parent_enrolment_id'] ?? null,
            'timestamp'           => isset($options['timestamp']) ? $options['timestamp'] : time(),
        ]);

        return $id = $options['id'] ?? $db->lastInsertId('gc_enrolment_revision');
    }
}
