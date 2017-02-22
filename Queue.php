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
    const DELETE_EVENTS = [
        self::PORTAL_DELETE,
        self::USER_DELETE,
        self::LO_DELETE,
        self::TAG_DELETE,
        self::ENROLMENT_DELETE,
    ];

    # The entity events
    # -------
    const PORTAL_CREATE         = 'portal.create';
    const PORTAL_UPDATE         = 'portal.update';
    const PORTAL_DELETE         = 'portal.delete';
    const PORTAL_CONFIG_CREATE  = 'portal-config.create';
    const PORTAL_CONFIG_UPDATE  = 'portal-config.update';
    const PORTAL_CONFIG_DELETE  = 'portal-config.delete';
    const CONTRACT_CREATE       = 'contract.create';
    const CONTRACT_UPDATE       = 'contract.create';
    const LO_CREATE             = 'lo.create'; # Body: LO object, no lo.items should be expected.
    const LO_UPDATE             = 'lo.update'; # Body: LO object with extra property: origin.
    const LO_DELETE             = 'lo.delete'; # Body: LO object.
    const USER_CREATE           = 'user.create';
    const USER_UPDATE           = 'user.update';
    const USER_DELETE           = 'user.delete';
    const RO_CREATE             = 'ro.create';
    const RO_UPDATE             = 'ro.update';
    const RO_DELETE             = 'ro.delete';
    const VOTE_CREATE           = 'vote.create';
    const VOTE_UPDATE           = 'vote.update';
    const VOTE_DELETE           = 'vote.delete';
    const CUSTOMER_CREATE       = 'customer.create';
    const CUSTOMER_UPDATE       = 'customer.update';
    const CUSTOMER_DELETE       = 'customer.delete';
    const ENROLMENT_CREATE      = 'enrolment.create';
    const ENROLMENT_UPDATE      = 'enrolment.update';
    const ENROLMENT_DELETE      = 'enrolment.delete';
    const TAG_CREATE            = 'tag.create';
    const TAG_UPDATE            = 'tag.update';
    const TAG_DELETE            = 'tag.delete';
    const TRANSACTION_CREATE    = 'transaction.create';
    const TRANSACTION_UPDATE    = 'transaction.update';
    const ASM_ASSIGNMENT_CREATE = 'asm.assignment.create';
    const ASM_ASSIGNMENT_UPDATE = 'asm.assignment.update';
    const ASM_ASSIGNMENT_DELETE = 'asm.assignment.delete';
    const ASM_SUBMISSION_CREATE = 'asm.submission.create';
    const ASM_SUBMISSION_UPDATE = 'asm.submission.update';
    const ASM_SUBMISSION_DELETE = 'asm.submission.delete';
    const ASM_FEEDBACK_CREATE   = 'asm.feedback.create';
    const ASM_FEEDBACK_UPDATE   = 'asm.feedback.update';
    const ASM_FEEDBACK_DELETE   = 'asm.feedback.delete';
    const ALGOLIA_LO_UPDATE     = 'algolia.lo.update'; # Lo Object {id: INT, type: STRING}
    const ALGOLIA_LO_DELETE     = 'algolia.lo.delete'; # Lo Object {id: INT, type: STRING}
    const ECK_CREATE            = 'eck.entity.create';
    const ECK_UPDATE            = 'eck.entity.update';
    const ECK_DELETE            = 'eck.entity.delete';

    # routingKey that tell some service to do something.
    # -------
    const DO_CONSUMER_HTTP_REQUEST             = 'do.consumer.HttpRequest'; # { method: STRING, url: STRING, query: STRING, headers: map[STRING][STRING], body: STRING }
    const DO_PUBLIC_API_WEBHOOK_REQUEST        = 'do.public-api.webhook-request'; # { appId: INT, url: STRING, subject: OBJECT, original: null|OBJECT }
    const DO_MAIL_SEND                         = 'do.mail.send'; # { subject: STRING, body: STRING, html: STRING, context: OBJECT, attachments: STRING[], options: OBJECT }
    const DO_HISTORY_RECORD                    = 'do.history.record';
    const DO_ENROLMENT_CRON                    = 'do.enrolment.cron'; # { task: STRING }
    const DO_ENROLMENT_CHECK_MODULE_ENROLMENTS = 'do.enrolment.check-module-enrolments'; # { moduleId: INT }
    const DO_ENROLMENT_CHECK_MODULE_ENROLMENT  = 'do.enrolment.check-module-enrolment'; # { moduleId: INT, enrolmentId: INT }
    const DO_ENROLMENT_CREATE                  = 'do.enrolment.create'; # { … }
    const DO_ENROLMENT_UPDATE                  = 'do.enrolment.update'; # { KEY_N: MIXED|NULL }
    const DO_SMS_SEND                          = 'do.sms.send'; # { to: STRING, body: STRING }
    const DO_GRAPHIN_IMPORT                    = 'do.graphin.import'; # { type: STRING, id: INT }
    const DO_USER_DELETE                       = 'do.user.delete'; # User Object
    const DO_ALGOLIA_INDEX                     = 'do.algolia.index'; # Object { offset: INT, limit: INT}
    const DO_USER_UNBLOCK_MAIL                 = 'do.user.unblock.mail'; # String mail
    const DO_USER_UNBLOCK_IP                   = 'do.user.unblock.ip'; # String ip
}
