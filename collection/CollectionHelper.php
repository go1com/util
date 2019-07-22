<?php

namespace go1\util\collection;

use Doctrine\DBAL\Connection;
use go1\util\DB;

class CollectionHelper
{
    public static function loadByPortalAndMachineName(Connection $db, int $portalId, string $machineName = CollectionTypes::DEFAULT):? Collection
    {
        $collection = $db
            ->executeQuery("SELECT * FROM collection_collection WHERE portal_id = ? AND machine_name = ?", [$portalId, $machineName], [DB::INTEGER, DB::STRING])
            ->fetch(DB::OBJ);

        return $collection ? Collection::create($collection) : null;
    }
}
