<?php

namespace go1\util\schema;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;
use go1\kv\KV;
use go1\util\DB;
use go1\util\dimensions\DimensionRepository;
use go1\util\plan\PlanRepository;

trait InstallTrait
{
    public function installGo1Schema(Connection $db, $coreOnly = true, string $accountsName = null)
    {
        DB::install($db, [
            function (Schema $schema) {
                if (!$schema->hasTable('gc_kv')) {
                    if (class_exists(KV::class)) {
                        KV::migrate($schema, 'gc_kv');
                    }
                }

                if (!$schema->hasTable('gc_ro')) {
                    $edge = $schema->createTable('gc_ro');
                    $edge->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
                    $edge->addColumn('type', 'integer', ['unsigned' => true]);
                    $edge->addColumn('source_id', 'integer', ['unsigned' => true]);
                    $edge->addColumn('target_id', 'integer', ['unsigned' => true]);
                    $edge->addColumn('weight', 'integer', ['unsigned' => true]);
                    $edge->addColumn('data', 'text', ['notnull' => false]);
                    $edge->setPrimaryKey(['id']);
                    $edge->addIndex(['source_id']);
                    $edge->addIndex(['target_id']);
                    $edge->addUniqueIndex(['type', 'source_id', 'target_id']);
                }

                if (!$schema->hasTable('gc_access')) {
                    $access = $schema->createTable('gc_access');
                    $access->addColumn('group_id', Type::INTEGER, ['unsigned' => true]);
                    $access->addColumn('instance_id', Type::INTEGER, ['unsigned' => true]);
                    $access->addColumn('entity_type', Type::STRING);
                    $access->addColumn('entity_id', Type::INTEGER, ['unsigned' => true]);
                    $access->addColumn('user_id', Type::INTEGER, ['unsigned' => true]);
                    $access->addIndex(['instance_id']);
                    $access->addIndex(['entity_type']);
                    $access->addIndex(['entity_id']);
                    $access->addIndex(['user_id']);
                    $access->addUniqueIndex(['group_id', 'entity_type', 'entity_id', 'user_id']);
                }

                PortalSchema::install($schema);
                UserSchema::install($schema);
                LoSchema::install($schema);
                EnrolmentSchema::install($schema);
                PlanRepository::install($schema);
                DimensionRepository::install($schema);
            },
            function (Schema $schema) use ($coreOnly) {
                if (!$coreOnly) {
                    class_exists(SocialSchema::class) && SocialSchema::install($schema);
                    class_exists(NoteSchema::class) && NoteSchema::install($schema);
                    class_exists(VoteSchema::class) && VoteSchema::install($schema);
                    class_exists(ContractSchema::class) && ContractSchema::install($schema);
                }
            },
        ]);

        if ($accountsName) {
            UserSchema::createViews($db, $accountsName);
        }
    }
}
