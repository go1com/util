<?php

namespace go1\util;

use Doctrine\DBAL\Connection;

class LoChecker
{
    public function isAuthor(Connection $db, int $loId, int $userId)
    {
        $sql = 'SELECT 1 FROM gc_ro WHERE source_id = ? AND type = ? AND target_id = ?';

        return $db->fetchColumn($sql, [$loId, EdgeTypes::HAS_AUTHOR_EDGE, $userId]) ? true : false;
    }
}
