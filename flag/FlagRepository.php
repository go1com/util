<?php

namespace go1\util\flag;

use Doctrine\DBAL\Connection;
use go1\clients\MqClient;
use go1\util\DB;
use go1\util\queue\Queue;
use Psr\Log\LoggerInterface;

class FlagRepository
{
    private $logger;
    private $db;
    private $queue;

    public function __construct(LoggerInterface $logger, Connection $db, MqClient $queue)
    {
        $this->logger = $logger;
        $this->db = $db;
        $this->queue = $queue;
    }

    public function db(): Connection
    {
        return $this->db;
    }

    public function browse(
        string $entityType = 'lo',
        int $entityId = null,
        int $userId = null,
        int $reason = null,
        int $level = null,
        int $limit = 50,
        int $offset = 0,
        string $orderBy = null,
        string $direction = 'ASC'): array
    {
        $q = $this
            ->db
            ->createQueryBuilder()
            ->select('DISTINCT flag.*, item.*')
            ->from('flag')
            ->innerJoin('flag', 'flag_item', 'item', 'flag.flag_id = item.id')
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        if (null !== $orderBy) {
            $q->orderBy("flag.{$orderBy}", $direction);
        }

        if ($entityType) {
            $q
                ->andWhere('item.entity_type = :entity_type')
                ->setParameter(':entity_type', $entityType);
        }

        if ($entityId) {
            $q
                ->andWhere('item.entity_id = :entity_id')
                ->setParameter(':entity_id', $entityId);
        }

        if ($userId) {
            $q
                ->andWhere('flag.user_id = :user_id')
                ->setParameter(':user_id', $userId);
        }

        if (null !== $reason) {
            $q
                ->andWhere('flag.reason = :reason')
                ->setParameter(':reason', $reason);
        }

        if (null !== $level) {
            $q
                ->andWhere('flag.level = :level')
                ->setParameter(':level', $level);
        }

        $q = $q->execute();
        while ($row = $q->fetch(DB::OBJ)) {
            $rows[] = Flag::create($row);
        }

        return $rows ?? [];
    }

    public function load(int $id)
    {
        $flag = "SELECT * FROM flag WHERE id = ?";
        $flag = $this->db->executeQuery($flag, [$id])->fetch(DB::OBJ);
        $flag = $flag ? Flag::create($flag) : false;

        return $flag;
    }

    public function loadFlagItem(string $entityType, int $entityId)
    {
        $flagItem = "SELECT * FROM flag_item WHERE entity_type = ? AND entity_id = ?";
        $flagItem = $this->db->executeQuery($flagItem, [$entityType, $entityId])->fetch(DB::OBJ);

        return $flagItem;
    }

    public function create(Flag &$flag): int
    {
        $flag->flagId = $this->assignFlagToEntity($flag);

        $row = $flag->jsonSerialize();
        unset($row['entity_type']);
        unset($row['entity_id']);

        $this->db->insert('flag', $row);
        $flag->id = $this->db->lastInsertId('flag');

        $this->queue->publish($flag, Queue::FLAG_CREATE);

        return $flag->id;
    }

    public function update(Flag $flag): bool
    {
        if (!$original = $this->load($flag->id)) {
            return false;
        }

        if (!$diff = $original->diff($flag)) {
            return false;
        }

        return DB::transactional($this->db, function () use (&$flag, &$diff, &$original) {
            if ($diff) {
                $diff['updated'] = time();
                $this->db->update('flag', $diff, ['id' => $flag->id]);

                $flagItem = $this->loadFlagItem($flag->entityType, $flag->entityId);
                if ($flag->level > $flagItem->level) {
                    $this->db->update('flag_item', ['level' => $flag->level], ['id' => $flag->flagId]);
                }
            }

            $flag->original = $original;
            $this->queue->publish($flag, Queue::FLAG_UPDATE);

            return true;
        });
    }

    private function assignFlagToEntity(Flag $flag): int
    {
        $flagItem = $this->loadFlagItem($flag->entityType, $flag->entityId);
        if ($flagItem) {
            $flagId = $flagItem->id;

            if ($flag->level > $flagItem->level) {
                $this->db->update('flag_item', ['level' => $flag->level], ['id' => $flagId]);
            }
        } else {
            $this->db->insert('flag_item', [
                'entity_type' => $flag->entityType,
                'entity_id'   => $flag->entityId,
                'level'       => $flag->level
            ]);
            $flagId = $this->db->lastInsertId('flag');
        }

        return $flagId;
    }
}
