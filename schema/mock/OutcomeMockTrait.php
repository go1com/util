<?php

namespace go1\util\schema\mock;

use Doctrine\DBAL\Connection;

trait OutcomeMockTrait
{
    public function createOutcome(Connection $db, array $options)
    {
        $statusInProgress = 2;

        $db->insert('gc_outcome', [
            'lo_id'           => isset($options['lo_id']) ? $options['lo_id'] : 111,
            'profile_id'      => isset($options['profile_id']) ? $options['profile_id'] : 222,
            'outcome'         => isset($options['outcome']) ? $options['outcome'] : $statusInProgress,
            'completion_rate' => isset($options['completion_rate']) ? $options['completion_rate'] : 0,
            'remote_id'       => isset($options['remote_id']) ? $options['remote_id'] : null,
        ]);

        return $db->lastInsertId('gc_outcome');
    }
}
