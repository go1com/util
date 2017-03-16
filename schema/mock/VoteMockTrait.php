<?php

namespace go1\util\schema\mock;

use Doctrine\DBAL\Connection;
use go1\util\DB;
use go1\util\vote\VoteHelper;

trait VoteMockTrait
{
    protected function createVote(Connection $db, $type, $entityType, $entityId, $profileId, $value)
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
        $this->cacheVote($db, $id);

        return $id;
    }

    private function cacheVote(Connection $db, int $voteId)
    {
        $vote = VoteHelper::load($db, $voteId);
        $data = VoteHelper::getCacheData($db, $vote->type, $vote->entity_type, $vote->entity_id);
        if (!$data) {
            return;
        }
        $percent = VoteHelper::calculatePercent($vote->type, $data);

        $voteCache = $db->executeQuery(
            'SELECT `data` FROM vote_caches WHERE type = ? AND entity_type = ? AND entity_id = ?',
            [$vote->type, $vote->entity_type, $vote->entity_id]
        )->fetchColumn();

        if ($voteCache) {
            $db->update(
                'vote_caches',
                [
                    'data'    => json_encode($data),
                    'percent' => $percent,
                ],
                [
                    'type'        => $vote->type,
                    'entity_type' => $vote->entity_type,
                    'entity_id'   => $vote->entity_id,
                ]
            );
        }
        else {
            $db->insert(
                'vote_caches',
                [
                    'type'        => $vote->type,
                    'entity_type' => $vote->entity_type,
                    'entity_id'   => $vote->entity_id,
                    'data'        => json_encode($data),
                    'percent'     => $percent,
                ]
            );
        }
    }
}
