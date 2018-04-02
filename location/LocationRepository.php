<?php

namespace go1\util\location;

use Doctrine\DBAL\Connection;
use go1\clients\MqClient;
use go1\util\DB;
use go1\util\queue\Queue;

class LocationRepository
{
    private $db;
    private $queue;

    public function __construct(Connection $db, MqClient $queue)
    {
        $this->db = $db;
        $this->queue = $queue;
    }

    public function load(int $id): ?Location
    {
        return ($location = $this->loadMultiple([$id])) ? $location[0] : null;
    }

    public function loadMultiple(array $ids)
    {
        $locations = $this->db
            ->executeQuery('SELECT * FROM gc_location WHERE id IN (?)', [$ids], [DB::INTEGERS])
            ->fetchAll(DB::OBJ);

        return array_map(function($_) {
                return Location::create($_);
            }, $locations);
    }

    public function create(Location &$location): int
    {
        $this->db->insert('gc_location', $location->jsonSerialize());
        $location->id = $this->db->lastInsertId('gc_location');
        $this->queue->publish($location->jsonSerialize(), Queue::LOCATION_CREATE);

        return $location->id;
    }

    public function update(Location $location): bool
    {
        if (!$original = $this->load($location->id)) {
            return false;
        }

        $this->db->update('gc_location', $location->jsonSerialize(), ['id' => $location->id]);
        $location->original = $original;
        $this->queue->publish($location->jsonSerialize(), Queue::LOCATION_UPDATE);

        return true;
    }

    public function delete(int $id): bool
    {
        if (!$location = $this->load($id)) {
            return false;
        }

        DB::transactional($this->db, function (Connection $db) use (&$location) {
            $db->delete('gc_location', ['id' => $location->id]);
            $this->queue->publish($location->jsonSerialize(), Queue::LOCATION_DELETE);
        });

        return true;
    }
}
