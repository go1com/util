<?php

namespace go1\util\schema\mock;

use Doctrine\DBAL\Connection;
use go1\util\dimensions\DimensionsHelper;

trait DimesionsMockTrait
{
    public function createDimension(Connection $db, array $options = [])
    {
        $db->insert('dimensions', [
            'id' => $options['id'] ?? null,
            'parent_id' => $options['parent_id'] ?? null,
            'name' => $options['name'],
            'type' => $options['type'],
            'created_date' => $options['created_date'],
            'modified_date' => $options['modified_date'],
        ]);

        return $db->lastInsertId('dimensions');
    }
}
