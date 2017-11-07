<?php

namespace go1\util\schema\mock;

use Doctrine\DBAL\Connection;
use go1\util\vote\VoteHelper;

trait VoteMockTrait
{
    protected function createVote(Connection $db, string $type, string $entityType, $entityId, $profileId, $value)
    {
        $db->insert('vote_items', [
            'type'        => $type,
            'entity_type' => $entityType,
            'entity_id'   => $entityId,
            'profile_id'  => $profileId,
            'value'       => $value,
            'timestamp'   => time(),
        ]);
        $id = $db->lastInsertId('vote_items');
        $this->cacheVote($db, $type, $entityType, $entityId);

        return $id;
    }

    protected function editVote(Connection $db, array $values, int $id, string $type, string $entityType, $entityId)
    {
        $db->update('vote_items', $values, ['id' => $id]);
        $this->cacheVote($db, $type, $entityType, $entityId);

        return $id;
    }

    protected function deleteVote(Connection $db, int $id, string $type, string $entityType, $entityId)
    {
        $db->delete('vote_items', ['id' => $id]);
        $this->cacheVote($db, $type, $entityType, $entityId);

        return $id;
    }

    private function cacheVote(Connection $db, string $type, string $entityType, $entityId)
    {
        $data = VoteHelper::getCacheData($db, $type, $entityType, $entityId);
        if (!$data) {
            return;
        }
        if (isset($data['dismiss'])) {
            unset($data['dismiss']);
        }
        $percent = VoteHelper::calculatePercent($type, $data);

        $voteCache = $db->executeQuery(
            'SELECT `data` FROM vote_caches WHERE type = ? AND entity_type = ? AND entity_id = ?',
            [$type, $entityType, $entityId]
        )->fetchColumn();

        if ($voteCache) {
            $db->update(
                'vote_caches',
                [
                    'data'    => json_encode($data),
                    'percent' => $percent,
                ],
                [
                    'type'        => $type,
                    'entity_type' => $entityType,
                    'entity_id'   => $entityId,
                ]
            );
        }
        else {
            $db->insert(
                'vote_caches',
                [
                    'type'        => $type,
                    'entity_type' => $entityType,
                    'entity_id'   => $entityId,
                    'data'        => json_encode($data),
                    'percent'     => $percent,
                ]
            );
        }
    }
}
