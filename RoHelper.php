<?php

namespace go1\util;

use BadFunctionCallException;
use Doctrine\DBAL\Connection;
use go1\clients\MqClient;

class RoHelper
{
    public static function link(Connection $db, MqClient $mqClient, $type, $sourceId, $targetId, $weight = 0, $data = null)
    {
        $db->insert('gc_ro', $edge = [
          'type'      => $type,
          'source_id' => $sourceId,
          'target_id' => $targetId,
          'weight'    => $weight,
          'data'      => is_scalar($data) ? $data : json_encode($data),
        ]);

        $mqClient->publish($edge, Queue::RO_CREATE);
    }

    public static function hasLink(Connection $db, $type, $sourceId, $targetId)
    {
        return $db->fetchColumn('SELECT 1 FROM gc_ro WHERE type = ? AND source_id = ? AND target_id = ?', [$type, $sourceId, $targetId]);
    }

    public static function unlink(Connection $db, MqClient $mqClient, $type, $sourceId = null, $targetId = null)
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
            $mqClient->publish($row, Queue::RO_DELETE);
        }
    }
}
