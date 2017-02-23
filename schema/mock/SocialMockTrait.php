<?php

namespace go1\util\schema\mock;

use Doctrine\DBAL\Connection;

trait SocialMockTrait
{
    public function createGroup(Connection $db, string $title, int $authorId, string $type, int $instance_id)
    {
        $db->insert('gc_social_group', [
            'type'          => $type,
            'title'         => $title,
            'user_id'       => $authorId,
            'instance_id'   => $instance_id,
            'created'       => time(),
        ]);

        return $db->lastInsertId('gc_social_group');
    }
}
