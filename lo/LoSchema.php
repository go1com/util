<?php

namespace go1\util\lo;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;

class LoSchema
{
    public static function install(Schema $schema)
    {
        if (!$schema->hasTable('gc_lo')) {
            $lo = $schema->createTable('gc_lo');
            $lo->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
            $lo->addColumn('type', 'string');
            $lo->addColumn('language', 'string');
            $lo->addColumn('instance_id', 'integer', ['unsigned' => true]);
            $lo->addColumn('remote_id', 'integer');
            $lo->addColumn('origin_id', 'integer');
            $lo->addColumn('title', 'string');
            $lo->addColumn('description', 'text');
            $lo->addColumn('private', 'smallint');
            $lo->addColumn('published', 'smallint');
            $lo->addColumn('marketplace', 'smallint');
            $lo->addColumn('image', 'string', ['notnull' => false]);
            $lo->addColumn('event', 'text', ['notnull' => false]);
            $lo->addColumn('event_start', 'integer', ['unsigned' => true, 'notnull' => false]);
            $lo->addColumn('locale', 'string', ['notnull' => false]);
            $lo->addColumn('tags', 'string');
            $lo->addColumn('enrolment_count', 'integer', ['unsigned' => true, 'notnull' => false, 'default' => 0, 'description' => 'Cache the number of enrolment, so that we can sort it faster.']);
            $lo->addColumn('timestamp', 'integer', ['unsigned' => true]);
            $lo->addColumn('data', 'blob');
            $lo->addColumn('created', 'integer');
            $lo->addColumn('updated', 'integer', ['notnull' => false]);
            $lo->addColumn('sharing', 'smallint');

            $lo->setPrimaryKey(['id']);
            $lo->addIndex(['type']);
            $lo->addIndex(['instance_id']);
            $lo->addIndex(['title']);
            $lo->addIndex(['language']);
            $lo->addIndex(['private']);
            $lo->addIndex(['published']);
            $lo->addIndex(['marketplace']);
            $lo->addIndex(['event_start']);
            $lo->addIndex(['timestamp']);
            $lo->addIndex(['created']);
            $lo->addIndex(['updated']);
            $lo->addIndex(['sharing']);
            $lo->addIndex(['enrolment_count']);
            $lo->addIndex(['tags']);
            $lo->addIndex(['locale']);
            $lo->addUniqueIndex(['instance_id', 'type', 'remote_id']);
        }

        if (!$schema->hasTable('gc_lo_pricing')) {
            $price = $schema->createTable('gc_lo_pricing');
            $price->addColumn('id', 'integer', ['unsigned' => true]);
            $price->addColumn('price', 'float');
            $price->addColumn('currency', 'string', ['length' => 4]);
            $price->addColumn('tax', 'float');
            $price->addColumn('tax_included', 'smallint', ['default' => 1]);
            $price->addColumn('tax_display', 'smallint', ['notnull' => false, 'default' => 1]);
            $price->addColumn('recurring', Type::BLOB, ['notnull' => false]);
            $price->setPrimaryKey(['id']);
            $price->addIndex(['price']);
        } else {
            $price = $schema->getTable('gc_lo_pricing');
            if (!$price->hasColumn('recurring')) {
                $price->addColumn('recurring', Type::BLOB, ['notnull' => false]);
            }
        }

        if (!$schema->hasTable('gc_lo_group')) {
            $group = $schema->createTable('gc_lo_group');
            $group->addColumn('lo_id', 'integer', ['unsigned' => true]);
            $group->addColumn('instance_id', 'integer', ['unsigned' => true]);
            $group->setPrimaryKey(['lo_id', 'instance_id']);
            $group->addIndex(['lo_id']);
            $group->addIndex(['instance_id']);
            $group->addForeignKeyConstraint('gc_lo', ['lo_id'], ['id']);
            $group->addForeignKeyConstraint('gc_instance', ['instance_id'], ['id']);
        }

        if (!$schema->hasTable('gc_event')) {
            $event = $schema->createTable('gc_event');
            $event->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
            $event->addColumn('start', 'string');
            $event->addColumn('end', 'string', ['notnull' => false]);
            $event->addColumn('timezone', 'string', ['length' => 3]);
            $event->addColumn('seats', 'integer', ['notnull' => false]);
            $event->addColumn('loc_country', 'string', ['notnull' => false]);
            $event->addColumn('loc_administrative_area', 'string', ['notnull' => false]);
            $event->addColumn('loc_sub_administrative_area', 'string', ['notnull' => false]);
            $event->addColumn('loc_locality', 'string', ['notnull' => false]);
            $event->addColumn('loc_dependent_locality', 'string', ['notnull' => false]);
            $event->addColumn('loc_thoroughfare', 'string', ['notnull' => false]);
            $event->addColumn('loc_premise', 'string', ['notnull' => false]);
            $event->addColumn('loc_sub_premise', 'string', ['notnull' => false]);
            $event->addColumn('loc_organisation_name', 'string', ['notnull' => false]);
            $event->addColumn('loc_name_line', 'string', ['notnull' => false]);
            $event->addColumn('loc_postal_code', 'integer', ['notnull' => false]);
            $event->addColumn('created', 'integer');
            $event->addColumn('updated', 'integer');
            $event->addColumn('data', 'blob');

            $event->setPrimaryKey(['id']);
            $event->addIndex(['start']);
            $event->addIndex(['end']);
            $event->addIndex(['loc_country']);
            $event->addIndex(['loc_administrative_area']);
            $event->addIndex(['loc_sub_administrative_area']);
            $event->addIndex(['loc_locality']);
            $event->addIndex(['loc_dependent_locality']);
            $event->addIndex(['loc_thoroughfare']);
            $event->addIndex(['loc_premise']);
            $event->addIndex(['loc_sub_premise']);
            $event->addIndex(['loc_organisation_name']);
            $event->addIndex(['loc_name_line']);
            $event->addIndex(['loc_postal_code']);
            $event->addIndex(['created']);
            $event->addIndex(['updated']);
        }

        if (!$schema->hasTable('gc_location')) {
            $location = $schema->createTable('gc_location');
            $location->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
            $location->addColumn('title', 'string');
            $location->addColumn('instance_id', 'integer', ['unsigned' => true]);
            $location->addColumn('country', 'string', ['notnull' => false]);
            $location->addColumn('administrative_area', 'string', ['notnull' => false]);
            $location->addColumn('sub_administrative_area', 'string', ['notnull' => false]);
            $location->addColumn('locality', 'string', ['notnull' => false]);
            $location->addColumn('dependent_locality', 'string', ['notnull' => false]);
            $location->addColumn('thoroughfare', 'string', ['notnull' => false]);
            $location->addColumn('premise', 'string', ['notnull' => false]);
            $location->addColumn('sub_premise', 'string', ['notnull' => false]);
            $location->addColumn('organisation_name', 'string', ['notnull' => false]);
            $location->addColumn('name_line', 'string', ['notnull' => false]);
            $location->addColumn('postal_code', 'integer', ['notnull' => false]);
            $location->addColumn('author_id', 'integer', ['notnull' => false]);
            $location->addColumn('created', 'integer');
            $location->addColumn('updated', 'integer');

            $location->setPrimaryKey(['id']);
            $location->addIndex(['title']);
            $location->addIndex(['instance_id']);
            $location->addIndex(['country']);
            $location->addIndex(['administrative_area']);
            $location->addIndex(['sub_administrative_area']);
            $location->addIndex(['locality']);
            $location->addIndex(['dependent_locality']);
            $location->addIndex(['thoroughfare']);
            $location->addIndex(['premise']);
            $location->addIndex(['sub_premise']);
            $location->addIndex(['organisation_name']);
            $location->addIndex(['name_line']);
            $location->addIndex(['postal_code']);
            $location->addIndex(['author_id']);
        }

        // @deprecated store custom tag
        if (!$schema->hasTable('gc_lo_tag')) {
            $customTag = $schema->createTable('gc_lo_tag');
            $customTag->addColumn('instance_id', Type::INTEGER);
            $customTag->addColumn('lo_id', Type::INTEGER);
            $customTag->addColumn('tag', Type::STRING);
            $customTag->addColumn('status', Type:: BOOLEAN);
            $customTag->addIndex(['instance_id']);
            $customTag->addIndex(['lo_id']);
            $customTag->addIndex(['tag']);
            $customTag->addUniqueIndex(['instance_id', 'lo_id', 'tag']);
        }

        // @deprecated
        if (!$schema->hasTable('gc_tag')) {
            $tag = $schema->createTable('gc_tag');
            $tag->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
            $tag->addColumn('title', 'string');
            $tag->addColumn('instance_id', 'integer', ['unsigned' => true]);
            $tag->addColumn('parent_id', 'integer', ['unsigned' => true]);
            $tag->addColumn('children', 'text', ['description' => 'Children IDs, separated by comma.', 'notnull' => false]);
            $tag->addColumn('lo_count', 'integer', ['default' => 0, 'description' => '@TODO: We do not really need this.']);
            $tag->addColumn('weight', 'integer', ['default' => 0]);
            $tag->addColumn('timestamp', 'integer', ['unsigned' => true]);
            $tag->setPrimaryKey(['id']);
            $tag->addUniqueIndex(['instance_id', 'title']);
            $tag->addIndex(['instance_id']);
            $tag->addIndex(['parent_id']);
            $tag->addIndex(['weight']);
            $tag->addIndex(['timestamp']);
            $tag->addForeignKeyConstraint('gc_instance', ['instance_id'], ['id']);
        }

        // All LO tags will be stored in this table
        if (!$schema->hasTable('gc_tags')) {
            $tags = $schema->createTable('gc_tags');
            $tags->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
            $tags->addColumn('title', 'string');
            $tags->addColumn('lo_id', 'integer', ['unsigned' => true]);
            $tags->addColumn('instance_id', 'integer', ['unsigned' => true]);
            $tags->addColumn('type', 'smallint');
            $tags->addColumn('created', 'integer', ['unsigned' => true]);
            $tags->setPrimaryKey(['id']);
            $tags->addIndex(['title']);
            $tags->addIndex(['lo_id']);
            $tags->addIndex(['instance_id']);
            $tags->addIndex(['type']);
            $tags->addIndex(['created']);
        }

        if (!$schema->hasTable('gc_lo_attributes')) {
            $table = $schema->createTable('gc_lo_attributes');
            $table->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
            $table->addColumn('lo_id', 'integer', ['unsigned' => true]);
            $table->addColumn('key', 'integer', ['unsigned' => true]);  /** @see \go1\util\lo\LoAttributes */
            $table->addColumn('value', 'string');
            $table->addColumn('created', 'integer', ['unsigned' => true]);
            $table->setPrimaryKey(['id']);
            $table->addIndex(['lo_id']);
            $table->addIndex(['key']);
        }

        if (!$schema->hasTable('lo_stream')) {
            $stream = $schema->createTable('lo_stream');
            $stream->addColumn('id', Type::INTEGER, ['unsigned' => true, 'autoincrement' => true]);
            $stream->addColumn('portal_id', Type::INTEGER, ['unsigned' => true]);
            $stream->addColumn('created', Type::INTEGER, ['unsigned' => true]);
            $stream->addColumn('lo_id', Type::INTEGER, ['unsigned' => true]);
            $stream->addColumn('action', Type::STRING);
            $stream->addColumn('payload', Type::BLOB);
            $stream->setPrimaryKey(['id']);
            $stream->addIndex(['lo_id']);
            $stream->addIndex(['portal_id']);
            $stream->addIndex(['created']);
        }
    }
}
