<?php

namespace go1\util\schema\mock;

use Doctrine\DBAL\Connection;
use go1\util\collection\CollectionStatus;
use go1\util\collection\CollectionTypes;

trait CollectionMockTrait
{
    protected function createCollection(Connection $db, array $options = [])
    {
        $data = $options['data'] ?? [];
        $data = !$data ? json_encode(null) : (is_scalar($data) ? $data : json_encode($data));

        $db->insert('collection_collection', [
            'id'           => $options['id'] ?? null,
            'type'         => $options['type'] ?? CollectionTypes::DEFAULT,
            'machine_name' => $options['machine_name'] ?? 'default',
            'title'        => $options['title'] ?? 'Default Collection',
            'portal_id'    => $options['portal_id'] ?? null,
            'author_id'    => $options['author_id'] ?? 1,
            'status'       => $options['status'] ?? CollectionStatus::ENABLED,
            'data'         => json_encode($data),
            'timestamp'    => $options['timestamp'] ?? time(),
            'created'      => $options['created'] ?? time(),
            'updated'      => $options['created'] ?? time(),
        ]);
        return $db->lastInsertId('collection_collection');
    }

    protected function createCollectionItem(Connection $db, array $options = [])
    {
        $db->insert('collection_collection_item', [
            'id'            => $options['id'] ?? null,
            'collection_id' => $options['collection_id'] ?? null,
            'lo_id'         => $options['lo_id'] ?? null,
            'timestamp'     => $options['timestamp'] ?? time(),
        ]);
        return $db->lastInsertId('collection_collection_item');
    }
}
