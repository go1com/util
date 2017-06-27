<?php

namespace go1\util\schema\mock;

use Doctrine\DBAL\Connection;
use go1\util\award\AwardStatuses;

trait AwardMockTrait
{
    protected function createAward(Connection $db, array $options = [])
    {
        $db->insert('award_award', [
            'revision_id' => $revisionId = isset($options['revision_id']) ? $options['revision_id'] : null,
            'instance_id' => isset($options['instance_id']) ? $options['instance_id'] : 0,
            'user_id'     => isset($options['user_id']) ? $options['user_id'] : 0,
            'title'       => isset($options['title']) ? $options['title'] : 'Example award',
            'description' => isset($options['description']) ? $options['description'] : '…',
            'tags'        => isset($options['tags']) ? $options['tags'] : '',
            'locale'      => isset($options['locale']) ? $options['locale'] : null,
            'data'        => isset($options['data']) ? $options['data'] : '',
            'published'   => isset($options['published']) ? $options['published'] : 1,
            'quantity'    => isset($options['quantity']) ? round($options['quantity'], 2) : null,
            'expire'      => isset($options['expire']) ? $options['expire'] : null,
            'created'     => isset($options['created']) ? $options['created'] : time(),
        ]);
        $awardId = $db->lastInsertId('award_award');
        $revisionId = $this->createAwardRevision($db, $awardId, $revisionId);

        $db->update('award_award', ['revision_id' => $revisionId], ['id' => $awardId]);

        if (isset($options['items']) && is_array($options['items'])) {
            foreach ($options['items'] as $item) {
                $this->createAwardItem($db, $revisionId, $item['entity_id'], $item['quantity']);
            }
        }

        return $awardId;
    }

    protected function createAwardRevision(Connection $db, int $awardId, int $id = null)
    {
        $db->insert('award_revision', array_filter(['id' => $id, 'award_id' => $awardId, 'updated' => time()]));

        return $db->lastInsertId('award_revision');
    }

    protected function createAwardItem(Connection $db, int $awardRevId, int $entityId, float $quantity = null)
    {
        $db->insert('award_item', [
            'award_revision_id' => $awardRevId,
            'entity_id'         => $entityId,
            'quantity'          => $quantity ? round($quantity, 2) : $quantity,
        ]);

        return $db->lastInsertId('award_item');
    }

    protected function createAwardAchievement(Connection $db, int $userId, int $awardItemId, int $created = null)
    {
        $db->insert('award_achievement', [
            'user_id'       => $userId,
            'award_item_id' => $awardItemId,
            'created'       => $created ?? time(),
        ]);

        return $db->lastInsertId('award_achievement');
    }

    protected function createAwardItemManual(Connection $db, array $options)
    {
        $options['data'] = isset($options['data'])
            ? (is_scalar($options['data']) ? json_decode($options['data'], true) : $options['data'])
            : [];
        $options['data'] = json_encode($options['data']);

        $db->insert('award_item_manual', [
            'award_id'        => $options['award_id'] ?? 0,
            'description'     => $options['description'] ?? null,
            'user_id'         => $options['user_id'] ?? 0,
            'entity_id'       => $options['entity_id'] ?? null,
            'verified'        => $options['verified'] ?? false,
            'verifier_id'     => $options['verifier_id'] ?? 0,
            'quantity'        => isset($options['quantity']) ? round($options['quantity'], 2) : null,
            'completion_date' => $options['completion_date'] ?? time(),
            'data'            => $options['data'],
            'published'       => $options['published'] ?? AwardStatuses::PUBLISHED,
        ]);

        return $db->lastInsertId('award_item_manual');
    }
}
