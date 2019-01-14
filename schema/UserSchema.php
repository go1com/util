<?php

namespace go1\util\schema;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\View;
use Doctrine\DBAL\Types\Type;
use go1\flood\Flood;

class UserSchema
{
    public static function install(Schema $schema)
    {
        if (!$schema->hasTable('gc_user')) {
            $user = $schema->createTable('gc_user');
            $user->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
            $user->addColumn('uuid', 'string');
            $user->addColumn('instance', 'string');
            $user->addColumn('profile_id', 'integer', ['notnull' => false]);
            $user->addColumn('mail', 'string');
            $user->addColumn('password', 'string');
            $user->addColumn('created', 'integer');
            $user->addColumn('access', 'integer');
            $user->addColumn('login', 'integer');
            $user->addColumn('status', 'integer');
            $user->addColumn('first_name', 'string');
            $user->addColumn('last_name', 'string');
            $user->addColumn('allow_public', 'integer', ['default' => 0]);
            $user->addColumn('data', 'text');
            $user->addColumn('timestamp', 'integer');

            $user->setPrimaryKey(['id']);
            $user->addIndex(['uuid']);
            $user->addIndex(['mail']);
            $user->addIndex(['created']);
            $user->addIndex(['login']);
            $user->addIndex(['timestamp']);
            $user->addIndex(['instance']);
            $user->addUniqueIndex(['uuid']);
            $user->addUniqueIndex(['instance', 'mail']);
            $user->addUniqueIndex(['instance', 'profile_id']);
        }

        if (!$schema->hasTable('gc_role')) {
            $role = $schema->createTable('gc_role');
            $role->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
            $role->addColumn('instance', 'string');
            $role->addColumn('rid', 'integer', ['unsigned' => true]);
            $role->addColumn('name', 'string');
            $role->addColumn('weight', 'integer', ['size' => 'tiny', 'default' => 0]);
            $role->addColumn('permission', 'text', ['notnull' => false]);
            $role->setPrimaryKey(['id']);
            $role->addIndex(['instance', 'name', 'weight']);
        }

        if (!$schema->hasTable('gc_user_locale')) {
            $locale = $schema->createTable('gc_user_locale');
            $locale->addColumn('id', 'integer', ['unsigned' => true]);
            $locale->addColumn('locale', 'string', ['length' => 12]);
            $locale->addColumn('weight', 'integer', ['unsigned' => true, 'default' => 0]);
            $locale->setPrimaryKey(['id', 'locale']);
            $locale->addIndex(['locale']);
            $locale->addIndex(['weight']);
            $locale->addForeignKeyConstraint('gc_user', ['id'], ['id']);
        }

        if (!$schema->hasTable('gc_user_mail')) {
            $mail = $schema->createTable('gc_user_mail');
            $mail->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
            $mail->addColumn('title', 'string');
            $mail->setPrimaryKey(['id']);
            $mail->addIndex(['title']);
        }

        if (!$schema->hasTable('gc_flood')) {
            if (class_exists(Flood::class)) {
                Flood::migrate($schema, 'gc_flood');
            }
        }

        if (!$schema->hasTable('user_stream')) {
            $stream = $schema->createTable('user_stream');
            $stream->addColumn('id', Type::INTEGER, ['unsigned' => true, 'autoincrement' => true]);
            $stream->addColumn('created', Type::INTEGER, ['unsigned' => true]);
            $stream->addColumn('user_id', Type::INTEGER, ['unsigned' => true]);
            $stream->addColumn('action', Type::STRING);
            $stream->addColumn('payload', Type::BLOB);
            $stream->setPrimaryKey(['id']);
            $stream->addIndex(['user_id']);
            $stream->addIndex(['created']);
        }

        if (!$schema->hasTable('account_stream')) {
            $stream = $schema->createTable('account_stream');
            $stream->addColumn('id', Type::INTEGER, ['unsigned' => true, 'autoincrement' => true]);
            $stream->addColumn('portal_id', Type::INTEGER, ['unsigned' => true]);
            $stream->addColumn('created', Type::INTEGER, ['unsigned' => true]);
            $stream->addColumn('account_id', Type::INTEGER, ['unsigned' => true]);
            $stream->addColumn('action', Type::STRING);
            $stream->addColumn('payload', Type::BLOB);
            $stream->setPrimaryKey(['id']);
            $stream->addIndex(['portal_id']);
            $stream->addIndex(['account_id']);
            $stream->addIndex(['created']);
        }
    }

    public static function createViews(Connection $db, string $accountsName)
    {
        $manager = $db->getSchemaManager();
        $manager->createView(new View('gc_users', "SELECT * FROM gc_user WHERE instance = '{$accountsName}'"));
        $manager->createView(new View('gc_accounts', "SELECT * FROM gc_user WHERE instance <> '{$accountsName}'"));
    }
}
