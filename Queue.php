<?php

namespace go1\util;

/**
 * Note for developers who are publishing messages:
 *
 * - "Create" message must contain the full entity, not only ID.
 * - "Update" message must contain 'original' value.
 * - "Delete" message must contain the full entity, not only ID.
 */
class Queue
{
    # The entity events
    # -------
    const PORTAL_CREATE      = 'portal.create';
    const PORTAL_UPDATE      = 'portal.update';
    const PORTAL_DELETE      = 'portal.delete';
    const LO_CREATE          = 'lo.create'; # Body: LO object, no lo.items should be expected.
    const LO_UPDATE          = 'lo.update'; # Body: LO object with extra property: origin.
    const LO_DELETE          = 'lo.delete'; # Body: LO object.
    const USER_CREATE        = 'user.create';
    const USER_UPDATE        = 'user.update';
    const USER_DELETE        = 'user.delete';
    const RO_CREATE          = 'ro.create';
    const RO_UPDATE          = 'ro.update';
    const RO_DELETE          = 'ro.delete';
    const VOTE_CREATE        = 'vote.create';
    const VOTE_UPDATE        = 'vote.update';
    const VOTE_DELETE        = 'vote.delete';
    const CUSTOMER_CREATE    = 'customer.create';
    const CUSTOMER_UPDATE    = 'customer.update';
    const CUSTOMER_DELETE    = 'customer.delete';
    const ENROLMENT_CREATE   = 'enrolment.create';
    const ENROLMENT_UPDATE   = 'enrolment.update';
    const ENROLMENT_DELETE   = 'enrolment.delete';
    const TAG_CREATE         = 'tag.create';
    const TAG_UPDATE         = 'tag.update';
    const TAG_DELETE         = 'tag.delete';
    const TRANSACTION_CREATE = 'transaction.create';
    const TRANSACTION_UPDATE = 'transaction.update';

    # routingKey that tell some service to do something.
    # -------
    const DO_CONSUMER_HTTP_REQUEST             = 'do.consumer.HttpRequest'; # { method: STRING, url: STRING, query: STRING, headers: map[STRING][STRING], body: STRING }
    const DO_PUBLIC_API_WEBHOOK_REQUEST        = 'do.public-api.webhook-request'; # { appId: INT, url: STRING, subject: OBJECT, original: null|OBJECT }
    const DO_MAIL_SEND                         = 'do.mail.send'; # { subject: STRING, body: STRING, html: STRING, context: OBJECT, attachments: STRING[], options: OBJECT }
    const DO_HISTORY_RECORD                    = 'do.history.record';
    const DO_ENROLMENT_CHECK_MODULE_ENROLMENTS = 'do.enrolment.check-module-enrolments'; # { moduleId: INT }
    const DO_ENROLMENT_CHECK_MODULE_ENROLMENT  = 'do.enrolment.check-module-enrolment'; # { moduleId: INT, enrolmentId: INT }
    const DO_ENROLMENT_CREATE                  = 'do.enrolment.create'; # { … }
    const DO_ENROLMENT_UPDATE                  = 'do.enrolment.update'; # { KEY_N: MIXED|NULL }
    const DO_SMS_SEND                          = 'do.sms.send'; # { to: STRING, body: STRING }
    const DO_GRAPHIN_IMPORT                    = 'do.graphin.import'; # { type: STRING, id: INT }
}
