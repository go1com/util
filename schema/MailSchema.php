<?php

namespace go1\util\schema;

use Doctrine\DBAL\Schema\Schema;

class MailSchema
{
    public static function install(Schema $schema)
    {
        if (!$schema->hasTable('mail_account')) {
            $account = $schema->createTable('mail_account');
            $account->addColumn('instance', 'string');
            $account->addColumn('data', 'blob');
            $account->setPrimaryKey(['instance']);
        }

        if (!$schema->hasTable('mail_log')) {
            $log = $schema->createTable('mail_log');
            $log->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
            $log->addColumn('instance', 'integer');
            $log->addColumn('recipient', 'string');
            $log->addColumn('sender', 'string');
            $log->addColumn('cc', 'string');
            $log->addColumn('bcc', 'string');
            $log->addColumn('subject', 'string');
            $log->addColumn('context', 'text');
            $log->addColumn('options', 'text');
            $log->addColumn('attachments', 'string');
            $log->addColumn('timestamp', 'integer', ['unsigned' => true]);
            $log->setPrimaryKey(['id']);
            $log->addIndex(['instance']);
            $log->addIndex(['recipient']);
            $log->addIndex(['sender']);
            $log->addIndex(['cc']);
            $log->addIndex(['bcc']);
            $log->addIndex(['subject']);
            $log->addIndex(['timestamp']);
        }

        if (!$schema->hasTable('mail_department')) {
            $department = $schema->createTable('mail_department');
            $department->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
            $department->addColumn('application', 'string');
            $department->addColumn('department', 'string');
            $department->addColumn('recipient', 'string');
            $department->setPrimaryKey(['id']);
            $department->addUniqueIndex(['application', 'department'], 'uniq_application_department');
        }

        // Schema v1.1, add smtp_id column
        if ($schema->hasTable('mail_log') && $log = $schema->getTable('mail_log')) {
            if (!$log->hasColumn('smtp_id')) {
                $log->addColumn('smtp_id', 'string', ['not_null' => false]);
                $log->addIndex(['smtp_id']);
            }
            // Schema v1.2, remove body,html column (By allow NULL for now)
            foreach (['body', 'html'] as $key) {
                if ($log->hasColumn($key) && $col = $log->getColumn($key)) {
                    if ($col->getNotnull()) {
                        $col->setNotnull(false);
                    }
                }
            }
        }

        // Schema v1.3, add category column (By allow NULL for now)
        if ($schema->hasTable('mail_log') && $log = $schema->getTable('mail_log')) {
            if (!$log->hasColumn('category')) {
                $log->addColumn('category', 'string', ['not_null' => false]);
            }
        }
    }
}
