<?php

namespace go1\util\schema\mock;

use Doctrine\DBAL\Connection;

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
            'quantity'    => isset($options['quantity']) ? $options['quantity'] : null,
            'expire'      => isset($options['expire']) ? $options['expire'] : null,
            'created'     => isset($options['created']) ? $options['created'] : time(),
        ]);
        $awardId = $db->lastInsertId('award_award');

        is_null($revisionId) && $db->update('award_award',
            ['revision_id' => $this->createAwardRevision($db, $awardId)],
            ['id' => $awardId]
        );

        return $awardId;
    }

    protected function createAwardRevision(Connection $db, int $awardId)
    {
        $db->insert('award_revision', ['award_id' => $awardId, 'updated' => time()]);

        return $db->lastInsertId('award_revision');
    }

    protected function createAwardItem(Connection $db, int $awardRevId, int $entityId, int $quantity = null)
    {
        $db->insert('award_item', [
            'award_revision_id' => $awardRevId,
            'entity_id'         => $entityId,
            'quantity'          => $quantity,
        ]);

        return $db->lastInsertId('award_item');
    }
}
