<?php

namespace go1\util\schema\mock;

use Doctrine\DBAL\Connection;
use go1\util\DateTime;
use go1\util\metric\MetricStatuses;
use go1\util\metric\MetricTypes;

trait MetricMockTrait
{
    protected function createMetric(Connection $db, array $options)
    {
        $db->insert('staff_metric', [
            'id'          => $options['id'] ?? null,
            'title'       => $options['title'] ?? 'Example metric',
            'type'        => $options['type'] ?? MetricTypes::NEW_ARR,
            'status'      => $options['status'] ?? MetricStatuses::ACTIVE,
            'value'       => $options['value'] ?? 1,
            'user_id'     => $options['user_id'] ?? 1,
            'start_date'  => $options['start_date'] ?? DateTime::formatDate(time()),
            'description' => $options['description'] ?? '',
            'created'     => $options['created'] ?? time(),
            'updated'     => $options['updated'] ?? time(),
        ]);

        return $db->lastInsertId('staff_metric');
    }

}
