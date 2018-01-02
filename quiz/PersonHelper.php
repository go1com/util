<?php

namespace go1\util\quiz;

use Doctrine\DBAL\Connection;
use go1\util\DB;

class PersonHelper
{
    public static function loadByExternalId(Connection $db, int $id, $source = 'go1.user')
    {
        $person = $db
            ->executeQuery('SELECT * FROM person WHERE external_identifier = ? AND external_source = ?', [$id, $source])
            ->fetch(DB::OBJ);

        return $person;
    }

    public static function loadBySecondaryId(Connection $db, int $id, $source = 'go1.user')
    {
        $person = $db
            ->executeQuery('SELECT * FROM person WHERE secondary_identifier = ? AND external_source = ?', [$id, $source])
            ->fetch(DB::OBJ);

        return $person;
    }
}
