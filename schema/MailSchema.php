<?php

namespace go1\util\schema;

use Doctrine\DBAL\Schema\Schema;

class MailSchema
{
    public static function install(Schema $schema)
    {
        $account = $schema->createTable('mail_account');
        $account->addColumn('instance', 'string');
        $account->addColumn('data', 'blob');
        $account->setPrimaryKey(['instance']);

        $log = $schema->createTable('mail_log');
        $log->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
        $log->addColumn('instance', 'integer');
        $log->addColumn('recipient', 'string');
        $log->addColumn('cc', 'string');
        $log->addColumn('bcc', 'string');
        $log->addColumn('subject', 'string');
        $log->addColumn('body', 'text');
        $log->addColumn('html', 'text');
        $log->addColumn('attachments', 'string');
        $log->addColumn('timestamp', 'integer', ['unsigned' => true]);
        $log->setPrimaryKey(['id']);
        $log->addIndex(['instance']);
        $log->addIndex(['recipient']);
        $log->addIndex(['cc']);
        $log->addIndex(['bcc']);
        $log->addIndex(['subject']);
        $log->addIndex(['timestamp']);
    }
}
