<?php

namespace go1\util\location;

use Doctrine\DBAL\Connection;
use go1\clients\MqClient;
use go1\util\DB;
use go1\util\queue\Queue;

class LocationRepository
{
    private $go1;
    private $queue;

    public function __construct(Connection $db, MqClient $queue)
    {
        $this->go1 = $db;
        $this->queue = $queue;
    }

    public function load(int $id): ?Location
    {
        return ($location = $this->loadMultiple([$id])) ? $location[0] : null;
    }

    public function loadMultiple(array $ids)
    {
        $locations = $this->go1
            ->executeQuery('SELECT * FROM gc_location WHERE id IN (?)', [$ids], [DB::INTEGERS])
            ->fetchAll(DB::OBJ);

        return array_map(function ($_) {
            return Location::create($_);
        }, $locations);
    }

    public function create(Location &$location): int
    {
        $this->go1->insert('gc_location', $location->jsonSerialize());
        $location->id = $this->go1->lastInsertId('gc_location');
        $this->queue->publish($location->jsonSerialize(), Queue::LOCATION_CREATE);

        return $location->id;
    }

    public function update(Location $location): bool
    {
        if (!$original = $this->load($location->id)) {
            return false;
        }

        $this->go1->update('gc_location', $location->jsonSerialize(), ['id' => $location->id]);
        $location->original = $original;
        $this->queue->publish($location->jsonSerialize(), Queue::LOCATION_UPDATE);

        return true;
    }

    public function delete(int $id): bool
    {
        if (!$location = $this->load($id)) {
            return false;
        }

        DB::transactional($this->go1, function (Connection $db) use (&$location) {
            $db->delete('gc_location', ['id' => $location->id]);
            $this->queue->publish($location->jsonSerialize(), Queue::LOCATION_DELETE);
        });

        return true;
    }

    public function relatedLearingObjectIds(Location $location): array
    {
        # const HAS_LOCATION                = 40; # T: gc_location.id       | S: gc_event.id
    }
}
