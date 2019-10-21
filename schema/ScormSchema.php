<?php

namespace go1\util\schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;

class ScormSchema
{
    public static function install(Schema $schema)
    {
        if (!$schema->hasTable('scorm_credential')) {
            $credential = $schema->createTable('scorm_credential');
            $credential->addColumn('id', Type::INTEGER, ['unsigned' => true, 'autoincrement' => true]);
            $credential->addColumn('instance', Type::STRING);
            $credential->addColumn('client_id', Type::STRING);
            $credential->addColumn('client_secret', Type::STRING);
            $credential->setPrimaryKey(['id']);
            $credential->addUniqueIndex(['instance']);
        }

        if (!$schema->hasTable('scorm_package')) {
            $package = $schema->createTable('scorm_package');
            $package->addColumn('id', Type::INTEGER, ['unsigned' => true, 'autoincrement' => true]);
            $package->addColumn('uuid', Type::STRING);
            $package->addColumn('pc_id', Type::INTEGER);
            $package->addColumn('lo_id', Type::INTEGER);
            $package->addColumn('created', Type::INTEGER, ['unsigned' => true]);
            $package->addColumn('updated', Type::INTEGER, ['unsigned' => true]);
            $package->addColumn('expire', Type::INTEGER, ['unsigned' => true]);
            $package->addColumn('data', Type::BLOB);
            $package->addColumn('status', Type::SMALLINT);
            $package->setPrimaryKey(['id']);
            $package->addUniqueIndex(['uuid']);
            $package->addIndex(['lo_id']);
            $package->addIndex(['pc_id']);
            $package->addIndex(['created']);
            $package->addIndex(['updated']);
            $package->addIndex(['expire']);
            $package->addIndex(['status']);
        }

        if (!$schema->hasTable('scorm_user')) {
            $scormUser = $schema->createTable('scorm_user');
            $scormUser->addColumn('id', Type::INTEGER, ['unsigned' => true, 'autoincrement' => true]);
            $scormUser->addColumn('user_id', Type::INTEGER, ['unsigned' => true]);
            $scormUser->addColumn('student_id', Type::STRING);
            $scormUser->addColumn('student_name', Type::STRING);
            $scormUser->addColumn('portal_id', Type::INTEGER, ['unsigned' => true]);
            $scormUser->addColumn('portal_name', Type::STRING);
            $scormUser->addColumn('timestamp', Type::INTEGER, ['unsigned' => true]);
            $scormUser->setPrimaryKey(['id']);
            $scormUser->addUniqueIndex(['user_id']);
            $scormUser->addIndex(['student_id']);
        }
    }
}
