<?php

namespace go1\util\queue;

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

    const NOTIFY_TASKS = [
        self::NOTIFY_PORTAL_USER_PLAN,
    ];

    # The entity events
    # -------
    const PORTAL_CREATE                = 'portal.create';
    const PORTAL_UPDATE                = 'portal.update';
    const PORTAL_DELETE                = 'portal.delete';
    const PORTAL_CONFIG_CREATE         = 'portal-config.create';
    const PORTAL_CONFIG_UPDATE         = 'portal-config.update';
    const PORTAL_CONFIG_DELETE         = 'portal-config.delete';
    const CONTRACT_CREATE              = 'contract.create';
    const CONTRACT_UPDATE              = 'contract.update';
    const CONTRACT_DELETE              = 'contract.delete';
    const CONTRACT_VIEW_LIST           = 'contract.view.list';
    const CONTRACT_VIEW_DETAIL         = 'contract.view.detail';
    const CONTRACT_VIEW_SUBSCRIPTION   = 'contract.view.subscription';
    const CONTRACT_CREATE_START        = 'contract.create.start';
    const CONTRACT_CREATE_END          = 'contract.create.end';
    const LO_CREATE                    = 'lo.create'; # Body: LO object, no lo.items should be expected.
    const LO_UPDATE                    = 'lo.update'; # Body: LO object with extra property: origin.
    const LO_DELETE                    = 'lo.delete'; # Body: LO object.
    const LO_SAVE_ASSESSORS            = 'lo.save.assessors';          # Body: {body: [create: INT[], update: INT[], delete: INT[]], id: INT}
    const USER_CREATE                  = 'user.create';
    const USER_UPDATE                  = 'user.update';
    const USER_DELETE                  = 'user.delete';
    const USER_FORGET_PASSWORD         = 'user.forget-password';
    const USER_LOGIN_SUCCESS           = 'user.login-success';
    const USER_LOGIN_FAIL              = 'user.login-fail';
    const USER_MASQUERADE              = 'user.masquerade';
    const USER_EMAIL_CREATE            = 'user-email.create';
    const USER_EMAIL_UPDATE            = 'user-email.update';
    const USER_EMAIL_DELETE            = 'user-email.delete';
    const RO_CREATE                    = 'ro.create';
    const RO_UPDATE                    = 'ro.update';
    const RO_DELETE                    = 'ro.delete';
    const VOTE_CREATE                  = 'vote.create';
    const VOTE_UPDATE                  = 'vote.update';
    const VOTE_DELETE                  = 'vote.delete';
    const CUSTOMER_CREATE              = 'customer.create';
    const CUSTOMER_UPDATE              = 'customer.update';
    const CUSTOMER_DELETE              = 'customer.delete';
    const CUSTOMER_VIEW_LIST           = 'customer.view.list';
    const CUSTOMER_VIEW_DETAIL         = 'customer.view.detail';
    const CUSTOMER_VIEW_PORTAL         = 'customer.view.portal';
    const CUSTOMER_VIEW_CONTRACT       = 'customer.view.contract';
    const CUSTOMER_VIEW_SUBSCRIPTION   = 'customer.view.subscription';
    const CUSTOMER_CREATE_START        = 'customer.create.start';
    const CUSTOMER_CREATE_END          = 'customer.create.end';
    const PLAN_CREATE                  = 'plan.create';
    const PLAN_UPDATE                  = 'plan.update';
    const PLAN_DELETE                  = 'plan.delete';
    const ENROLMENT_CREATE             = 'enrolment.create';
    const ENROLMENT_UPDATE             = 'enrolment.update';
    const ENROLMENT_DELETE             = 'enrolment.delete';
    const ENROLMENT_REVISION_CREATE    = 'enrolment-revision.create';
    const ENROLMENT_SAVE_ASSESSORS     = 'enrolment.save.assessors';   # Body: {body: [create: INT[], update: INT[], delete: INT[]], id: INT}
    const MANUAL_RECORD_CREATE         = 'manual-record.create';
    const MANUAL_RECORD_UPDATE         = 'manual-record.update';
    const MANUAL_RECORD_DELETE         = 'manual-record.delete';
    const ONBOARD_COMPLETE             = 'onboard.complete';
    const TAG_CREATE                   = 'tag.create';
    const TAG_UPDATE                   = 'tag.update';
    const TAG_DELETE                   = 'tag.delete';
    const CUSTOM_TAG_PUSH              = 'custom-tag.push'; # Body: {instance_id: INT, lo_id: INT}
    const CUSTOM_TAG_CREATE            = 'custom-tag.create';
    const CUSTOM_TAG_UPDATE            = 'custom-tag.update';
    const CUSTOM_TAG_DELETE            = 'custom-tag.delete';
    const COUPON_CREATE                = 'coupon.create';
    const COUPON_UPDATE                = 'coupon.update';
    const COUPON_DELETE                = 'coupon.delete';
    const COUPON_USE                   = 'coupon.use';
    const TRANSACTION_CREATE           = 'transaction.create';
    const TRANSACTION_UPDATE           = 'transaction.update';
    const ASM_ASSIGNMENT_CREATE        = 'asm.assignment.create';
    const ASM_ASSIGNMENT_UPDATE        = 'asm.assignment.update';
    const ASM_ASSIGNMENT_DELETE        = 'asm.assignment.delete';
    const ASM_SUBMISSION_CREATE        = 'asm.submission.create';
    const ASM_SUBMISSION_UPDATE        = 'asm.submission.update';
    const ASM_SUBMISSION_DELETE        = 'asm.submission.delete';
    const ASM_FEEDBACK_CREATE          = 'asm.feedback.create';
    const ASM_FEEDBACK_UPDATE          = 'asm.feedback.update';
    const ASM_FEEDBACK_DELETE          = 'asm.feedback.delete';
    const ALGOLIA_LO_UPDATE            = 'algolia.lo.update'; # Lo Object {id: INT, type: STRING}
    const ALGOLIA_LO_DELETE            = 'algolia.lo.delete'; # Lo Object {id: INT, type: STRING}
    const ECK_CREATE                   = 'eck.entity.create';
    const ECK_UPDATE                   = 'eck.entity.update';
    const ECK_DELETE                   = 'eck.entity.delete';
    const ECK_METADATA_CREATE          = 'eck.metadata.create';
    const ECK_METADATA_UPDATE          = 'eck.metadata.update';
    const ECK_METADATA_DELETE          = 'eck.metadata.delete';
    const FLAG_CREATE                  = 'flag.create';
    const FLAG_UPDATE                  = 'flag.update';
    const FLAG_DELETE                  = 'flag.delete';
    const GROUP_CREATE                 = 'group.create';
    const GROUP_UPDATE                 = 'group.update';
    const GROUP_DELETE                 = 'group.delete';
    const GROUP_ITEM_CREATE            = 'group.item.create';
    const GROUP_ITEM_UPDATE            = 'group.item.update';
    const GROUP_ITEM_DELETE            = 'group.item.delete';
    const GROUP_ASSIGN_CREATE          = 'group.assign.create';
    const GROUP_ASSIGN_UPDATE          = 'group.assign.update';
    const GROUP_ASSIGN_DELETE          = 'group.assign.delete';
    const HISTORY_RECORD               = 'history.record';
    const NOTE_CREATE                  = 'note.create';
    const NOTE_UPDATE                  = 'note.update';
    const NOTE_DELETE                  = 'note.delete';
    const NOTIFY_CONFIG_CREATE         = 'notify_config.create';
    const NOTIFY_CONFIG_SAVE           = 'notify_config.save';
    const REPORT_CREATE                = 'report.create';
    const REPORT_UPDATE                = 'report.update';
    const REPORT_DELETE                = 'report.delete';
    const AWARD_CREATE                 = 'award.create';
    const AWARD_UPDATE                 = 'award.update';
    const AWARD_DELETE                 = 'award.delete';
    const AWARD_ITEM_CREATE            = 'award.item.create';
    const AWARD_ITEM_UPDATE            = 'award.item.update';
    const AWARD_ITEM_DELETE            = 'award.item.delete';
    const AWARD_ITEM_MANUAL_CREATE     = 'award.item-manual.create';
    const AWARD_ITEM_MANUAL_UPDATE     = 'award.item-manual.update';
    const AWARD_ITEM_MANUAL_DELETE     = 'award.item-manual.delete';
    const AWARD_ACHIEVEMENT_CREATE     = 'award.achievement.create';
    const AWARD_ACHIEVEMENT_UPDATE     = 'award.achievement.update';
    const AWARD_ACHIEVEMENT_DELETE     = 'award.achievement.delete';
    const AWARD_ENROLMENT_CREATE       = 'award.enrolment.create';
    const AWARD_ENROLMENT_UPDATE       = 'award.enrolment.update';
    const AWARD_ENROLMENT_DELETE       = 'award.enrolment.delete';
    const AWARD_ITEM_ENROLMENT_CREATE  = 'award.item.enrolment.create';
    const AWARD_ITEM_ENROLMENT_UPDATE  = 'award.item.enrolment.update';
    const AWARD_ITEM_ENROLMENT_DELETE  = 'award.item.enrolment.delete';
    const WORKER_QUEUE_NAME            = 'worker';
    const MAIL_LOG_CREATE              = 'mail-log.create';
    const NOTIFY_PORTAL_USER_PLAN      = 'notify.portal.user_plan_reached';
    const QUIZ_USER_ANSWER_CREATE      = 'quiz.user_answer.create';
    const QUIZ_USER_ANSWER_UPDATE      = 'quiz.user_answer.update';
    const QUIZ_USER_ANSWER_DELETE      = 'quiz.user_answer.delete';
    const LOCATION_CREATE              = 'location.create';
    const LOCATION_UPDATE              = 'location.update';
    const LOCATION_DELETE              = 'location.delete';
    const LO_GROUP_CREATE              = 'lo_group.create';
    const LO_GROUP_DELETE              = 'lo_group.delete';
    const CREDIT_CREATE                = 'credit.create';
    const CREDIT_UPDATE                = 'credit.update';
    const CREDIT_DELETE                = 'credit.delete';
    const CREDIT_USAGE_CREATE          = 'credit_usage.create';
    const ROLE_CREATE                  = 'role.create';
    const ROLE_UPDATE                  = 'role.update';
    const ROLE_DELETE                  = 'role.delete';
    const ACTIVITY_CREATE              = 'activity.create';
    const ACTIVITY_UPDATE              = 'activity.update';
    const ACTIVITY_DELETE              = 'activity.delete';
    const METRIC_CREATE                = 'metric.create';
    const METRIC_UPDATE                = 'metric.update';
    const METRIC_DELETE                = 'metric.delete';
    const PAYMENT_STRIPE_AUTHORIZE     = 'payment.stripe.authorize';
    const PAYMENT_STRIPE_DEAUTHORIZE   = 'payment.stripe.deauthorize';
    const NOTE_COMMENT_CREATE          = 'note_comment.create';
    const NOTE_COMMENT_UPDATE          = 'note_comment.update';
    const NOTE_COMMENT_DELETE          = 'note_comment.delete';
    const GROUP_COLLECTION_CREATE      = 'group_collection.create';
    const GROUP_COLLECTION_UPDATE      = 'group_collection.update';
    const GROUP_COLLECTION_DELETE      = 'group_collection.delete';
    const GROUP_COLLECTION_ITEM_CREATE = 'group_collection_item.create';
    const GROUP_COLLECTION_ITEM_UPDATE = 'group_collection_item.update';
    const GROUP_COLLECTION_ITEM_DELETE = 'group_collection_item.delete';
    const POLICY_ITEM_CREATE           = 'policy.item.create';
    const POLICY_ITEM_UPDATE           = 'policy.item.update';
    const POLICY_ITEM_DELETE           = 'policy.item.delete';
    const PAGEUP_COURSE_UPLOAD         = 'pageup.course.upload';
    const EXIM_TASK_UPDATE             = 'exim.task.update';
    const PURCHASE_REQUEST_CREATE      = 'purchase.request.create';
    const PURCHASE_REQUEST_UPDATE      = 'purchase.request.update';
    const PURCHASE_REQUEST_DELETE      = 'purchase.request.delete';
    const EVENT_SESSION_CREATE         = 'event.session.create';
    const EVENT_SESSION_UPDATE         = 'event.session.update';
    const EVENT_SESSION_DELETE         = 'event.session.delete';
    const EVENT_LOCATION_CREATE        = 'event.location.create';
    const EVENT_LOCATION_UPDATE        = 'event.location.update';
    const EVENT_LOCATION_DELETE        = 'event.location.delete';

    # routingKey that tell some service to do something.
    #
    # Note
    # =======
    # We should not add a lot of routing keys for each task. Each should define only one DO routing key for each service.
    # For example:
    #   - Should not define:
    #       - DO_ENROLMENT_CHECK_MODULE_ENROLMENTS = 'do.enrolment.xxxxx' # { BODY }
    #       - DO_ENROLMENT_CHECK_MODULE_ENROLMENT  = 'do.enrolment.xxxxx' # { BODY }
    #   - Should:
    #       - DO_ENROLMENT = 'do.enrolment' # { task: TASK_NAME, body: TASK_BODY }
    #
    # The #consumer auto routing the message to #SERVICE when the routing key is "do.SERVICE".
    # -------
    const DO_CONSUMER_HTTP_REQUEST             = 'do.consumer.HttpRequest'; # { method: STRING, url: STRING, query: STRING, headers: map[STRING][STRING], body: STRING }
    const DO_FINDER                            = 'do.finder';
    const DO_PUBLIC_API_WEBHOOK_REQUEST        = 'do.public-api.webhook-request'; # { appId: INT, url: STRING, subject: OBJECT, original: null|OBJECT }
    const DO_MAIL_SEND                         = 'do.mail.send'; # { subject: STRING, body: STRING, html: STRING, context: OBJECT, attachments: STRING[], options: OBJECT }
    const DO_HISTORY_RECORD                    = 'do.history.record';
    const DO_ENROLMENT                         = 'process.enrolment'; # { action: STRING, body: OBJECT }
    const DO_ENROLMENT_CRON                    = 'do.enrolment.cron'; # { task: STRING }
    const DO_ENROLMENT_CHECK_MODULE_ENROLMENTS = 'do.enrolment.check-module-enrolments'; # { moduleId: INT }
    const DO_ENROLMENT_CHECK_MODULE_ENROLMENT  = 'do.enrolment.check-module-enrolment'; # { moduleId: INT, enrolmentId: INT }
    const DO_ENROLMENT_CREATE                  = 'do.enrolment.create'; # { … }
    const DO_ENROLMENT_UPDATE                  = 'do.enrolment.update'; # { KEY_N: MIXED|NULL }
    const DO_ENROLMENT_DELETE                  = 'do.enrolment.delete'; # { KEY_N: MIXED|NULL }
    const DO_ENROLMENT_PLAN_CREATE             = 'do.enrolment.plan.create'; # Plan Object
    const DO_EXIM_IMPORT_ENROLLMENT            = 'do.exim.import-enrolment'; # {user_id, lo_id, instance_id, notify, manager_id}
    const DO_EXIM_IMPORT_AWARD_ENROLLMENT      = 'do.exim.import-award-enrolment'; # {award_id, instance_id, user_ids}
    const DO_EXIM_IMPORT_USER                  = 'do.exim.import-user'; # {$instance, $mail, $first, $last, $status, $manager}
    const DO_EXIM_IMPORT                       = 'do.exim.import'; # { data: OBJECT[], taskId: INT }
    const DO_SMS_SEND                          = 'do.sms.send'; # { to: STRING, body: STRING }
    const DO_GRAPHIN_IMPORT                    = 'do.graphin.import'; # { type: STRING, id: INT }
    const DO_USER_CREATE_VIRTUAL_ACCOUNT       = 'do.user.virtual-account'; # { type: STRING, object: enrolment/??? object}
    const DO_USER_DELETE                       = 'do.user.delete'; # User Object
    const DO_USER_IMPORT                       = 'do.user.import'; # {$instance, $mail, $first, $last, $status, $manager}
    const DO_ALGOLIA_INDEX                     = 'do.algolia.index'; # Object { offset: INT, limit: INT}
    const DO_USER_UNBLOCK_MAIL                 = 'do.user.unblock.mail'; # String mail
    const DO_USER_UNBLOCK_IP                   = 'do.user.unblock.ip'; # String ip
    const DO_NOTIFY                            = 'do.notify'; # {task: string NOTIFY_TASKS, body: array TASK_BODY}
    const DO_AWARD_ITEM                        = 'do.award.item'; # { task: STRING, body: TASK_BODY }
    const DO_AWARD_CRON                        = 'do.award.cron'; # { task: STRING }
    const DO_AWARD_CALCULATE                   = 'do.award.calculate'; # {task: AWARD_TASK, body: array TASK_BODY}
    const DO_AWARD_PLAN_CREATE                 = 'do.award.plan.create'; # Plan Object
    const DO_INDEX                             = 'do.index'; # {index: STRING, type: string, operation: enum(index,create,update,delete,bulk), body: OBJECT, routing: STRING, parent: STRING}
    const DO_MYTEAM                            = 'process.my-team'; # { action: STRING, body: OBJECT }
    const DO_ASSESSOR                          = 'do.assessor'; # { task: string, body: OBJECT }
    const DO_PAGEUP_UPLOAD_COURSE              = 'do.pageup.upload-couse'; # { $portal_id, $course_id }

    public static function postEvent(string $event): string
    {
        return "post_{$event}";
    }
}
