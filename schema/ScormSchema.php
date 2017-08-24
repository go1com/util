<?php

namespace go1\util\schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;

class ScormSchema {
    public static function install(Schema $schema) {
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
            $package->addColumn('pc_id', Type::INTEGER, ['unsigned' => true, 'notnull'  => false]);
            $package->addColumn('lo_id', Type::INTEGER);
            $package->addColumn('created', Type::INTEGER);
            $package->addColumn('updated', Type::INTEGER, ['notnull' => false]);
            $package->addColumn('expire', Type::INTEGER);
            $package->addColumn('data', 'blob');
            $package->setPrimaryKey(['id']);
            $package->addUniqueIndex(['uuid']);
            $package->addIndex(['lo_id']);
            $package->addIndex(['pc_id']);
            $package->addIndex(['created']);
            $package->addIndex(['updated']);
            $package->addIndex(['expire']);
            $package->addForeignKeyConstraint($credential, ['pc_id'], ['id'], ['onUpdate' => 'CASCADE', 'onDelete' => 'CASCADE']);
        }
    }
}
