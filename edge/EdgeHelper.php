<?php

namespace go1\util\edge;

use BadFunctionCallException;
use Doctrine\DBAL\Connection;
use go1\clients\MqClient;
use go1\util\Queue;

class EdgeHelper
{
    private $select;

    public static function select(string $select = null)
    {
        $helper = new self;
        $helper->select = $select;

        return $helper;
    }

    public static function link(Connection $db, MqClient $queue, $type, $sourceId, $targetId, $weight = 0, $data = null)
    {
        $db->insert('gc_ro', $edge = [
            'type'      => $type,
            'source_id' => $sourceId,
            'target_id' => $targetId,
            'weight'    => $weight,
            'data'      => is_scalar($data) ? $data : json_encode($data),
        ]);

        $queue->publish($edge, Queue::RO_CREATE);
    }

    public static function hasLink(Connection $db, $type, $sourceId, $targetId)
    {
        return $db->fetchColumn('SELECT 1 FROM gc_ro WHERE type = ? AND source_id = ? AND target_id = ?', [$type, $sourceId, $targetId]);
    }

    public static function unlink(Connection $db, MqClient $queue, $type, $sourceId = null, $targetId = null)
    {
        if (!$sourceId && !$targetId) {
            throw new BadFunctionCallException('Require source or target.');
        }

        $q = $db
            ->createQueryBuilder()
            ->select('*')
            ->from('gc_ro')
            ->where('type = :type')->setParameter(':type', $type);

        $sourceId && $q->andWhere('source_id = :source_id')->setParameter(':source_id', $sourceId);
        $targetId && $q->andWhere('target_id = :target_id')->setParameter(':target_id', $targetId);
        $q = $q->execute();

        while ($row = $q->fetch(DB::OBJ)) {
            $db->executeQuery('DELETE FROM gc_ro WHERE id = ?', [$row->id]);
            $queue->publish($row, Queue::RO_DELETE);
        }
    }

    public static function edgesFromSource(Connection $db, int $sourceId, array $types = [])
    {
        return self::edgesFromSources($db, [$sourceId], $types);
    }

    public static function edgesFromSources(Connection $db, array $sourceIds, array $types = [])
    {
        return self::edges($db, $sourceIds, [], $types);
    }

    public static function edgesFromTarget(Connection $db, int $targetId, array $types = [])
    {
        return self::edgesFromTargets($db, [$targetId], $types);
    }

    public static function edgesFromTargets(Connection $db, array $targetIds, array $types = [])
    {
        return self::edges($db, [], $targetIds, $types);
    }

    private static function edges(Connection $db, array $sourceIds = [], array $targetIds = [], array $types = [])
    {
        return self::select('*')->get($db, $sourceIds, $targetIds, $types);
    }

    public function get(Connection $db, array $sourceIds = [], array $targetIds = [], array $types = [], $mode = DB::OBJ)
    {
        if (!$sourceIds && !$targetIds && !$types) {
            return [];
        }

        $q = $db
            ->createQueryBuilder()
            ->select($this->select ?: '*')
            ->from('gc_ro');

        $sourceIds && $q
            ->andWhere('source_id IN (:source_id)')
            ->setParameter(':source_id', $sourceIds, Connection::PARAM_INT_ARRAY);

        $targetIds && $q
            ->andWhere('target_id IN (:target_id)')
            ->setParameter(':target_id', $targetIds, Connection::PARAM_INT_ARRAY);

        $types && $q
            ->andWhere('type IN (:types)')
            ->setParameter(':types', $types, Connection::PARAM_INT_ARRAY);

        return $q->execute()->fetchAll($mode);
    }

    public function getSingle(Connection $db, array $sourceIds = [], array $targetIds = [], array $types = [], $mode = DB::OBJ)
    {
        $result = $this->get($db, $sourceIds, $targetIds, $types, $mode);

        return $result ? reset($result) : false;
    }
}
