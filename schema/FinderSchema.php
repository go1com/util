<?php

namespace go1\util\schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;

class FinderSchema
{
    public static function install(Schema $schema)
    {
        if (!$schema->hasTable('finder_oauth2')) {
            $oauth2 = $schema->createTable('finder_oauth2');
            $oauth2->addColumn('id', Type::INTEGER, ['unsigned' => true, 'autoincrement' => true]);
            $oauth2->addColumn('access_token', Type::STRING);
            $oauth2->addColumn('provider', Type::STRING);
            $oauth2->addColumn('instance', Type::STRING);
            $oauth2->addColumn('user_id', TYPE::INTEGER, ['unsigned' => true]);
            $oauth2->setPrimaryKey(['id']);
            $oauth2->addIndex(['instance']);
            $oauth2->addIndex(['provider']);
            $oauth2->addIndex(['user_id']);
        }

        if (!$schema->hasTable('finder_oauth2_state')) {
            $state = $schema->createTable('finder_oauth2_state');
            $state->addColumn('state', Type::STRING);
            $state->addColumn('instance', Type::STRING);
            $state->addColumn('user_id', Type::INTEGER, ['unsigned' => true]);
            $state->addColumn('timestamp', Type::INTEGER);
            $state->setPrimaryKey(['state']);
            $state->addIndex(['timestamp']);
        }

        if (!$schema->hasTable('finder_elucidat_map')) {
            $map = $schema->createTable('finder_elucidat_map');
            $map->addColumn('instance', Type::STRING);
            $map->addColumn('code', Type::STRING);
            $map->addColumn('user_id', Type::INTEGER);
            $map->addIndex(['instance']);
            $map->addIndex(['code']);
            $map->addIndex(['user_id']);
        }
    }
}
