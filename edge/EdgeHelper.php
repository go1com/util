<?php

namespace go1\util\edge;

use BadFunctionCallException;
use Doctrine\DBAL\Connection;
use go1\clients\MqClient;
use go1\util\DB;
use go1\util\model\Edge;
use go1\util\queue\Queue;

class EdgeHelper
{
    private $select;

    public static function load(Connection $db, int $id): ?Edge
    {
        $row = 'SELECT * FROM gc_ro WHERE id = ?';
        $row = $db->executeQuery($row, [$id])->fetch(DB::OBJ);

        return $row ? Edge::create($row) : null;
    }

    public static function changeType(Connection $db, MqClient $queue, int $id, int $newType, $log = null)
    {
        if ($edge = self::load($db, $id)) {
            $edge->original = clone $edge;
            $oldType = $edge->type;

            if (isset($edge->data->oldType)) {
                $edge->data->oldType->{$oldType} = time();
            } else {
                $edge->data->oldType[$oldType] = time();
            }
            $edge->type = $newType;
            ($log) && $edge->data->log[] = $log;

            $db->update(
                'gc_ro',
                ['type' => $newType, 'data' => json_encode($edge->data)],
                ['id' => $id]
            );

            $queue->publish($edge->jsonSerialize(), Queue::RO_UPDATE);
        }
    }

    public static function select(string $select = null)
    {
        $helper = new self;
        $helper->select = $select;

        return $helper;
    }

    public static function link(
        Connection $db,
        MqClient $queue,
        int $type,
        int $sourceId,
        int $targetId,
        int $weight = 0, $data = null, array $payload = []): int
    {
        $db->insert('gc_ro', $edge = [
            'type'      => $type,
            'source_id' => $sourceId,
            'target_id' => $targetId,
            'weight'    => $weight,
            'data'      => is_scalar($data) ? $data : json_encode($data),
        ]);

        $edge['id'] = $db->lastInsertId('gc_ro');
        $queue->publish(array_merge($edge, $payload), Queue::RO_CREATE);

        return $edge['id'];
    }

    public static function hasLink(Connection $db, $type, $sourceId, $targetId)
    {
        return $db->fetchColumn('SELECT id FROM gc_ro WHERE type = ? AND source_id = ? AND target_id = ?', [$type, $sourceId, $targetId]);
    }

    public static function remove(Connection $db, MqCLient $queue, Edge $edge)
    {
        DB::transactional($db, function () use ($db, $queue, $edge) {
            $db->executeQuery('DELETE FROM gc_ro WHERE id = ?', [$edge->id]);
            $queue->publish($edge->jsonSerialize(), Queue::RO_DELETE);
        });
    }

    public static function unlink(
        Connection $db,
        MqClient $queue,
        int $type,
        int $sourceId = null,
        int $targetId = null,
        int $weight = null): array
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
        $weight && $q->andWhere('weight = :weight')->setParameter(':weight', $weight);

        $q = $q->execute();
        $affectedRoIds = [];
        while ($row = $q->fetch(DB::OBJ)) {
            $edge = Edge::create($row);
            $affectedRoIds[] = $edge->id;
            $db->executeQuery('DELETE FROM gc_ro WHERE id = ?', [$edge->id]);
            $queue->publish($edge->jsonSerialize(), Queue::RO_DELETE);
        }

        return $affectedRoIds;
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

    public static function edges(Connection $db, array $sourceIds = [], array $targetIds = [], array $types = [])
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
