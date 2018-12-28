<?php
namespace go1\util\schema;

use Doctrine\DBAL\Schema\Schema;

class EventSchema
{
    public static function install(Schema $schema)
    {
        if (!$schema->hasTable('event_session')) {
            $session = $schema->createTable('event_session');
            $session->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true, 'comment' => 'The primary identifier of an event session.']);
            $session->addColumn('title', 'string', ['comment' => 'The title of this event.']);
            $session->addColumn('portal_id', 'integer', ['unsigned' => true, 'comment' => 'Portal Id']);
            $session->addColumn('lo_id', 'integer', ['comment' => 'The learning object of this event session']);
            $session->addColumn('location_id', 'integer', ['notnull' => false, 'comment' => 'Location id, ref: event_location.id']);
            $session->addColumn('start_at', 'datetime', ['comment' => 'The start date and time of this event session']);
            $session->addColumn('end_at', 'datetime', ['comment' => 'The end date and time of this event session']);
            $session->addColumn('timezone', 'string', ['comment' => 'The timezone']);
            $session->addColumn('url', 'text', ['notnull' => false, 'comment' => 'The location url']);
            $session->addColumn('instructor_ids', 'array', ['notnull' => false, 'comment' => 'The list of an instructor']);
            $session->addColumn('description', 'text', ['notnull' => false, 'comment' => 'The location details']);
            $session->addColumn('attendee_limit', 'integer', ['default' => 0, 'notnull' => false, 'comment' => 'The attendee limit']);
            $session->addColumn('data', 'blob', ['notnull' => false, 'comment' => 'Json encoded extra data.']);
            $session->addColumn('published', 'smallint', ['default' => 1, 'comment' => '0:archived, 1: published']);
            $session->addColumn('created_time', 'integer', ['comment' => 'The unix timestamp when the date was created.']);
            $session->addColumn('updated_time', 'integer', ['notnull' => false, 'comment' => 'The unix timestamp when the date was updated.']);

            $session->setPrimaryKey(['id']);
            $session->addIndex(['lo_id']);
            $session->addIndex(['location_id']);
            $session->addIndex(['start_at']);
            $session->addIndex(['end_at']);
            $session->addIndex(['attendee_limit']);
        }

        if (!$schema->hasTable('event_enrolment')) {
            $table = $schema->createTable('event_enrolment');
            $table->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true, 'comment' => 'The primary identifier of an event enrolment.']);
            $table->addColumn('user_id', 'integer', ['comment' => 'The User Id']);
            $table->addColumn('lo_id', 'integer', ['comment' => 'ref: gc_lo']);
            $table->addColumn('enrolment_id', 'integer', ['comment' => 'ref: gc_enrolment.id']);
            $table->addColumn('event_id', 'integer', ['comment' => 'ref: event_session.id']);
            $table->addColumn('revision_id', 'integer', ['comment' => 'The latest revision, ref: event_enrolment_revision']);
            $table->addColumn('portal_id', 'integer', ['comment' => 'Portal Id']);
            $table->addColumn('profile_id', 'integer', ['comment' => 'The profile id']);
            $table->addColumn('taken_portal_id', 'integer', ['comment' => 'Taken Portal Id']);
            $table->addColumn('start_at', 'datetime', ['comment' => 'The start date of this event enrolment']);
            $table->addColumn('end_at', 'datetime', ['comment' => 'The end date of this event enrolment']);
            $table->addColumn('status', 'string', ['comment' => 'The status of this event enrolment']);
            $table->addColumn('result', 'float', ['notnull' => false, 'comment' => 'The result of this event enrolment']);
            $table->addColumn('pass', 'smallint', ['comment' => 'The pass of this event enrolment']);
            $table->addColumn('changed_at', 'datetime', ['unsigned' => true, 'comment' => 'The changed of this event enrolment']);
            $table->addColumn('timestamp', 'integer', ['unsigned' => true, 'comment' => 'The timestamp']);
            $table->addColumn('data', 'blob', ['notnull' => false, 'comment' => 'Json encoded extra data.']);
            $table->addColumn('published', 'smallint', ['default' => 1, 'comment' => '0:archived, 1: published']);
            $table->addColumn('created_time', 'integer', ['comment' => 'The unix timestamp when the event enrolment was created.']);
            $table->addColumn('updated_time', 'integer', ['notnull' => false, 'comment' => 'The unix timestamp when the event enrolment was updated.']);

            $table->setPrimaryKey(['id']);
            $table->addIndex(['lo_id']);
            $table->addIndex(['user_id']);
            $table->addIndex(['event_id']);
            $table->addIndex(['portal_id']);
            $table->addIndex(['profile_id']);
            $table->addIndex(['taken_portal_id']);
        }

        if (!$schema->hasTable('event_enrolment_revision')) {
            $table = $schema->createTable('event_enrolment_revision');
            $table->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true, 'comment' => 'The primary identifier of an event enrolment.']);
            $table->addColumn('user_id', 'integer', ['comment' => 'The User Id']);
            $table->addColumn('lo_id', 'integer', ['comment' => 'ref: gc_lo.id']);
            $table->addColumn('event_enrolment_id', 'integer', ['comment' => 'The foreign key']);
            $table->addColumn('enrolment_id', 'integer', ['comment' => 'ref: gc_enrolment.id']);
            $table->addColumn('event_id', 'integer', ['comment' => 'ref: event_session.id']);
            $table->addColumn('portal_id', 'integer', ['comment' => 'Portal Id']);
            $table->addColumn('profile_id', 'integer', ['comment' => 'The profile id']);
            $table->addColumn('taken_portal_id', 'integer', ['comment' => 'Taken Portal Id']);
            $table->addColumn('start_at', 'datetime', ['comment' => 'The start date of this event enrolment']);
            $table->addColumn('end_at', 'datetime', ['comment' => 'The end date of this event enrolment']);
            $table->addColumn('status', 'string', ['comment' => 'The status of this event enrolment']);
            $table->addColumn('result', 'float', ['notnull' => false, 'comment' => 'The result of this event enrolment']);
            $table->addColumn('pass', 'smallint', ['comment' => 'The pass of this event enrolment']);
            $table->addColumn('changed_at', 'datetime', ['unsigned' => true, 'comment' => 'The changed of this event enrolment']);
            $table->addColumn('timestamp', 'integer', ['unsigned' => true, 'comment' => 'The timestamp']);
            $table->addColumn('data', 'blob', ['notnull' => false, 'comment' => 'Json encoded extra data.']);
            $table->addColumn('note', 'text', ['notnull' => false, 'comment' => 'The reason to create revision']);
            $table->addColumn('published', 'smallint', ['default' => 1, 'comment' => '0:archived, 1: published']);
            $table->addColumn('created_time', 'integer', ['comment' => 'The unix timestamp when the event enrolment was created.']);
            $table->addColumn('updated_time', 'integer', ['notnull' => false, 'comment' => 'The unix timestamp when the event enrolment was updated.']);

            $table->setPrimaryKey(['id']);
            $table->addIndex(['lo_id']);
            $table->addIndex(['user_id']);
            $table->addIndex(['event_id']);
            $table->addIndex(['portal_id']);
            $table->addIndex(['profile_id']);
            $table->addIndex(['taken_portal_id']);
        }

        if (!$schema->hasTable('event_location')) {
            $location = $schema->createTable('event_location');
            $location->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true, 'comment' => 'The primary identifier of an event location.']);
            $location->addColumn('title', 'string', ['comment' => 'The title of this event.']);
            $location->addColumn('portal_id', 'integer', ['unsigned' => true, 'comment' => 'The Portal Id.']);
            $location->addColumn('country', 'string', ['notnull' => false, 'comment' => 'The Country.']);
            $location->addColumn('administrative_area', 'string', ['notnull' => false, 'comment' => 'The Administrative area.']);
            $location->addColumn('sub_administrative_area', 'string', ['notnull' => false, 'comment' => 'The Subadministrative area.']);
            $location->addColumn('locality', 'string', ['notnull' => false, 'comment' => 'The locality.']);
            $location->addColumn('dependent_locality', 'string', ['notnull' => false, 'comment' => 'The dependent locality.']);
            $location->addColumn('thoroughfare', 'string', ['notnull' => false, 'comment' => 'The throughfare.']);
            $location->addColumn('premise', 'string', ['notnull' => false, 'comment' => 'The premise.']);
            $location->addColumn('sub_premise', 'string', ['notnull' => false, 'comment' => 'The sub premise Id.']);
            $location->addColumn('organisation_name', 'string', ['notnull' => false, 'comment' => 'The organisation name.']);
            $location->addColumn('name_line', 'string', ['notnull' => false, 'comment' => 'The name line.']);
            $location->addColumn('postal_code', 'integer', ['notnull' => false, 'comment' => 'The Portal Code.']);
            $location->addColumn('author_id', 'integer', ['notnull' => false, 'comment' => 'The Author Id.']);
            $location->addColumn('is_online', 'integer', ['default' => 0, 'comment' => 'Online or not']);
            $location->addColumn('latitude', 'string', ['notnull' => false, 'comment' => 'The latitude.']);
            $location->addColumn('longitude', 'string', ['notnull' => false, 'comment' => 'The longitude.']);
            $location->addColumn('data', 'blob', ['notnull' => false, 'comment' => 'Json encoded extra data.']);
            $location->addColumn('published', 'smallint', ['default' => 1, 'comment' => '0:archived, 1: published']);
            $location->addColumn('created_time', 'integer', ['comment' => 'The unix timestamp when the event location was created.']);
            $location->addColumn('updated_time', 'integer', ['notnull' => false, 'comment' => 'The unix timestamp when the event location was updated.']);

            $location->setPrimaryKey(['id']);
            $location->addIndex(['title']);
            $location->addIndex(['portal_id']);
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
            $location->addIndex(['latitude']);
            $location->addIndex(['longitude']);
            $location->addIndex(['postal_code']);
            $location->addIndex(['author_id']);
        }
    }
}
