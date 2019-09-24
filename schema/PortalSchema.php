<?php

namespace go1\util\schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;

class PortalSchema
{
    public static function install(Schema $schema, bool $installPortalConf = true)
    {
        if (!$schema->hasTable('gc_instance')) {
            $instance = $schema->createTable('gc_instance');
            $instance->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
            $instance->addColumn('title', 'string');
            $instance->addColumn('status', 'smallint');
            $instance->addColumn('is_primary', 'smallint');
            $instance->addColumn('version', 'string');
            $instance->addColumn('data', 'blob');
            $instance->addColumn('timestamp', 'integer', ['unsigned' => true]);
            $instance->addColumn('created', 'integer', ['unsigned' => true]);
            $instance->setPrimaryKey(['id']);
            $instance->addIndex(['title']);
            $instance->addIndex(['status']);
            $instance->addIndex(['is_primary']);
            $instance->addIndex(['timestamp']);
            $instance->addIndex(['created']);
        }

        if (!$schema->hasTable('gc_domain')) {
            $domain = $schema->createTable('gc_domain');
            $domain->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
            $domain->addColumn('title', 'string');
            $domain->setPrimaryKey(['id']);
            $domain->addIndex(['title'], 'index_title');
        }

        if (!$schema->hasTable('portal_data')) {
            $data = $schema->createTable('portal_data');
            $data->addColumn('id', 'integer', ['unsigned' => true]);
            $data->addColumn('state', 'string', ['notnull' => false]);
            $data->addColumn('type', 'string', ['notnull' => false]);
            $data->addColumn('channel', 'string', ['notnull' => false]);
            $data->addColumn('plan', 'string', ['notnull' => false]);
            $data->addColumn('industry', 'string', ['notnull' => false]);
            $data->addColumn('customer_id', 'string', ['notnull' => false]);
            $data->addColumn('salesforce_id', 'string', ['notnull' => false]);
            $data->addColumn('partner_id', 'string', ['notnull' => false]);
            $data->addColumn('conversion_date', 'integer', ['unsigned' => true, 'notnull' => false]);
            $data->addColumn('go_live_date', 'integer', ['unsigned' => true, 'notnull' => false]);
            $data->addColumn('expiry_date', 'integer', ['unsigned' => true, 'notnull' => false]);
            $data->addColumn('cancel_expiry_date', 'integer', ['unsigned' => true, 'notnull' => false]);
            $data->addColumn('partner_portal_id', 'integer', ['unsigned' => true, 'notnull' => false]);
            $data->addColumn('referrer', 'string', ['notnull' => false]);

            $data->setPrimaryKey(['id']);
            $data->addIndex(['state']);
            $data->addIndex(['type']);
            $data->addIndex(['channel']);
            $data->addIndex(['plan']);
            $data->addIndex(['industry']);
            $data->addIndex(['customer_id']);
            $data->addIndex(['salesforce_id']);
            $data->addIndex(['partner_id']);
            $data->addIndex(['conversion_date']);
            $data->addIndex(['go_live_date']);
            $data->addIndex(['expiry_date']);
            $data->addIndex(['cancel_expiry_date']);
            $data->addIndex(['partner_portal_id']);
            $data->addIndex(['referrer']);
        }

        if (!$schema->hasTable('portal_stream')) {
            $stream = $schema->createTable('portal_stream');
            $stream->addColumn('id', Type::INTEGER, ['unsigned' => true, 'autoincrement' => true]);
            $stream->addColumn('portal_id', Type::INTEGER, ['unsigned' => true]);
            $stream->addColumn('created', Type::INTEGER, ['unsigned' => true]);
            $stream->addColumn('action', Type::STRING);
            $stream->addColumn('payload', Type::BLOB);
            $stream->setPrimaryKey(['id']);
            $stream->addIndex(['portal_id']);
            $stream->addIndex(['created']);
        }

        $installPortalConf && self::installPortalConf($schema);
        self::update01($schema);
        self::update02($schema);
    }

    public static function installPortalConf(Schema $schema)
    {
        if (!$schema->hasTable('portal_conf')) {
            $conf = $schema->createTable('portal_conf');
            $conf->addColumn('instance', 'string');
            $conf->addColumn('namespace', 'string');
            $conf->addColumn('name', 'string');
            $conf->addColumn('public', 'smallint');
            $conf->addColumn('data', 'blob');
            $conf->addColumn('timestamp', 'integer');
            $conf->setPrimaryKey(['instance', 'namespace', 'name']);
            $conf->addIndex(['instance', 'namespace']);
            $conf->addIndex(['public']);
            $conf->addIndex(['timestamp']);
        }
    }

    public static function installPortalIntegration(Schema $schema)
    {
        if ($schema->hasTable('portal_integration')) {
            return;
        }

        $table = $schema->createTable('portal_integration');
        $table->addColumn('portal_id', 'integer')->setUnsigned(true);
        $table->addColumn('integration', 'string', ['length' => 45]);
        $table->setPrimaryKey(['portal_id', 'integration']);
        $table->addColumn('enabled', 'boolean');
        $table->addIndex(['integration', 'enabled'], 'idx_integration_enabled');
    }

    public static function update01(Schema $schema)
    {
        if ($schema->hasTable('portal_data')) {
            $portalData = $schema->getTable('portal_data');
            if (!$portalData->hasColumn('industry')) {
                $portalData->addColumn('industry', 'string', ['notnull' => false]);
                $portalData->addIndex(['industry']);
            }
        }
    }

    public static function update02(Schema $schema)
    {
        if ($schema->hasTable('portal_data')) {
            $portalData = $schema->getTable('portal_data');
            if (!$portalData->hasColumn('referrer')) {
                $portalData->addColumn('referrer', 'string', ['notnull' => false]);
                $portalData->addIndex(['referrer']);
            }
        }
    }
}
