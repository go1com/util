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
        int $instanceId = null,
        string $entityType = 'lo',
        int $entityId = 0,
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
            ->select('DISTINCT flag.*')
            ->from('flag')
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        if (null !== $orderBy) {
            $q->orderBy("flag.{$orderBy}", $direction);
        }

        if ($entityId) {
            $q
                ->innerJoin('flag', 'flag_item', 'item', 'flag.flag_id = item.id')
                ->andWhere('item.entity_id = :entity_id')
                ->andWhere('item.entity_type = :entity_type')
                ->setParameter(':entity_type', $entityType)
                ->setParameter(':entity_id', $entityId);
        }

        if ($instanceId) {
            $q
                ->andWhere('flag.instance_id = :instance_id')
                ->setParameter(':user_id', $instanceId);
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

    public function load($id)
    {
        $flag = "SELECT * FROM flag WHERE id = ?";
        $flag = $this->db->executeQuery($flag, [$id])->fetch(DB::OBJ);
        $flag = $flag ? Flag::create($flag) : false;

        return $flag;
    }

    public function create(Flag &$flag): int
    {
        $row = $flag->jsonSerialize();
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
            }

            $flag->original = $original;
            $this->queue->publish($flag, Queue::FLAG_UPDATE);

            return true;
        });
    }
}
