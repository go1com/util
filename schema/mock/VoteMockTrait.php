<?php

namespace go1\util\schema\mock;

use Doctrine\DBAL\Connection;
use go1\util\DB;
use go1\util\VoteHelper;

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
        $vote = $db
            ->executeQuery('SELECT * FROM vote_items WHERE id = ?', [$voteId])
            ->fetch(DB::OBJ);

        $cacheData = $db->executeQuery(
            'SELECT `data` FROM vote_caches WHERE type = ? AND entity_type = ? AND entity_id = ?',
            [$vote->type, $vote->entity_type, $vote->entity_id]
        )->fetchColumn();

        $data = VoteHelper::buildCacheData($vote->type, $vote->value, json_decode($cacheData, true));
        if (!$data) {
            return;
        }
        $percent = VoteHelper::calculatePercent($vote->type, $data);

        if ($cacheData) {
            $db->update(
                'vote_caches',
                [
                    'data' => json_encode($data),
                    'percent' => $percent
                ],
                [
                    'type' => $vote->type,
                    'entity_type' => $vote->entity_type,
                    'entity_id' => $vote->entity_id
                ]
            );
        } else {
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
