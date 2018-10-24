<?php

namespace go1\util\es;

use go1\util\enrolment\EnrolmentStatuses;
use go1\util\event\AttendanceStatuses;

/**
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/mapping-types.html
 */
class Schema
{
    const INDEX             = ES_INDEX;
    const ALL_INDEX         = ES_INDEX . '*';
    const MARKETPLACE_INDEX = ES_INDEX . '_marketplace';
    const GROUP_INDEX       = ES_INDEX . '_group';
    const LOG_INDEX         = ES_INDEX . '_log';
    const ACTIVITY_INDEX    = ES_INDEX . '_activity';
    const PORTALS_INDEX     = ES_INDEX . '_portal*';
    const PAYMENT_INDEX     = ES_INDEX . '_payment';

    # Indices for explore
    const EXPLORE_INDEX             = ES_INDEX . '_explore';
    const EXPLORE_GROUP_INDEX       = ES_INDEX . '_explore_group';
    const EXPLORE_MARKETPLACE_INDEX = ES_INDEX . '_explore_marketplace';

    const TEMP             = -32;
    const MAX_INPUT_LENGTH = 50;

    const DO_INDEX  = 'index';
    const DO_UPDATE = 'update';
    const DO_DELETE = 'delete';

    const T_BOOL                = 'boolean'; # Don't use this, because query_string will match true always, use T_INT instead.
    const T_SHORT               = 'short';
    const T_INT                 = 'integer';
    const T_FLOAT               = 'float';
    const T_DOUBLE              = 'double'; # Use double if you want to use aggregation feature.
    const T_TEXT                = 'text';
    const T_KEYWORD             = 'keyword';
    const T_DATE                = 'date';
    const T_ARRAY               = 'array';
    const T_COMPLETION          = 'completion';
    const T_COMPLETION_CATEGORY = 'CATEGORY'; # must be in upper-case
    const T_OBJECT              = 'object';
    const T_NESTED              = 'nested';
    const T_GEO_POINT           = 'geo_point';

    const O_EDGE                = 'edge';
    const O_PORTAL              = 'portal';
    const O_CONFIG              = 'configuration';
    const O_USER                = 'user';
    const O_ACCOUNT             = 'account';
    const O_ACTIVITY            = 'activity';
    const O_LO                  = 'lo';
    const O_LO_COLLECTION       = 'lo_collection';
    const O_PLAN                = 'plan';
    const O_ENROLMENT           = 'enrolment';
    const O_ENROLMENT_REVISION  = 'enrolment_revision';
    const O_SUBMISSION          = 'asm_submission';
    const O_SUBMISSION_REVISION = 'asm_submission_revision';
    const O_GROUP               = 'group';
    const O_GROUP_ITEM          = 'group_item';
    const O_MAIL                = 'mail';
    const O_PAYMENT_TRANSACTION = 'payment_transaction';
    const O_CREDIT              = 'credit';
    const O_QUIZ_USER_ANSWER    = 'quiz_user_answer';
    const O_PURCHASE_REQUEST    = 'purchase_request';
    const O_ECK_METADATA        = 'eck_metadata';
    const O_COUPON              = 'coupon';
    const O_LO_GROUP            = 'lo_group';
    const O_LO_POLICY           = 'lo_policy';
    const O_LO_TAG              = 'lo_tag';
    const O_EVENT               = 'event';
    const O_EVENT_ATTENDANCE    = 'event_attendance';
    const O_AWARD               = 'award';
    const O_AWARD_ITEM          = 'award_item';
    const O_AWARD_ITEM_MANUAL   = 'award_item_manual';
    const O_AWARD_ENROLMENT     = 'award_enrolment';
    const O_AWARD_ACHIEVEMENT   = 'award_achievement';
    const O_SUGGESTION_CATEGORY = 'suggestion_category'; # Suggestion for award manual item's category
    const O_SUGGESTION_TAG      = 'suggestion_tag'; # Suggestion for ES LO's tag
    const O_MYTEAM_PROGRESS     = 'myteam_progress';
    const O_CONTRACT            = 'contract';
    const O_METRIC              = 'metric';

    // enrolment only belongs to LO. account_enrolment is enrolment, but belong to account.
    // This is used to get users that is not enrolled to a course.
    const O_ACCOUNT_ENROLMENT = 'account_enrolment';

    const A_SIMPLE     = 'simple';
    const A_WHITESPACE = 'whitespace';

    const SCHEMA = [
        'index' => self::INDEX,
        'body'  => self::BODY,
    ];

    const BODY = [
        'mappings' => self::MAPPING,
    ];

    const MAPPING = [
        self::O_EDGE                => self::EDGE_MAPPING,
        self::O_PORTAL              => self::PORTAL_MAPPING,
        self::O_CONFIG              => self::CONFIGURATION_MAPPING,
        self::O_USER                => self::USER_MAPPING,
        self::O_ACCOUNT             => self::ACCOUNT_MAPPING,
        self::O_LO                  => self::LO_MAPPING,
        self::O_LO_GROUP            => self::LO_GROUP_MAPPING,
        self::O_LO_TAG              => self::LO_TAG_MAPPING,
        self::O_LO_POLICY           => self::LO_POLICY_MAPPING,
        self::O_PLAN                => self::PLAN_MAPPING,
        self::O_ENROLMENT           => self::ENROLMENT_MAPPING,
        self::O_ENROLMENT_REVISION  => self::ENROLMENT_MAPPING_REVISION,
        self::O_SUBMISSION          => self::SUBMISSION_MAPPING,
        self::O_SUBMISSION_REVISION => self::SUBMISSION_REVISION_MAPPING,
        self::O_GROUP               => self::GROUP_MAPPING,
        self::O_GROUP_ITEM          => self::GROUP_ITEM_MAPPING,
        self::O_MAIL                => self::MAIL_MAPPING,
        self::O_PAYMENT_TRANSACTION => self::PAYMENT_TRANSACTION_MAPPING,
        self::O_CREDIT              => self::CREDIT_MAPPING,
        self::O_QUIZ_USER_ANSWER    => self::QUIZ_USER_ANSWER_MAPPING,
        self::O_PURCHASE_REQUEST    => self::PURCHASE_REQUEST_MAPPING,
        self::O_ECK_METADATA        => self::ECK_METADATA_MAPPING,
        self::O_COUPON              => self::COUPON_MAPPING,
        self::O_EVENT               => self::EVENT_MAPPING,
        self::O_EVENT_ATTENDANCE    => self::EVENT_ATTENDANCE_MAPPING,
        self::O_AWARD               => self::AWARD_MAPPING,
        self::O_AWARD_ITEM          => self::AWARD_ITEM_MAPPING,
        self::O_AWARD_ITEM_MANUAL   => self::AWARD_ITEM_MANUAL_MAPPING,
        self::O_AWARD_ACHIEVEMENT   => self::AWARD_ACHIEVEMENT_MAPPING,
        self::O_ACCOUNT_ENROLMENT   => self::ACCOUNT_ENROLMENT_MAPPING,
        self::O_SUGGESTION_CATEGORY => self::SUGGESTION_CATEGORY_MAPPING,
        self::O_SUGGESTION_TAG      => self::SUGGESTION_TAG_MAPPING,
        self::O_MYTEAM_PROGRESS     => self::MY_TEAM_MAPPING,
        self::O_CONTRACT            => self::CONTRACT_MAPPING,
        self::O_METRIC              => self::METRIC_MAPPING,
        self::O_ACTIVITY            => self::ACTIVITY_MAPPING,
        self::O_LO_COLLECTION       => self::LO_COLLECTION_MAPPING,
    ];

    const ANALYZED = [
        'fields' => [
            'analyzed' => [
                'type' => self::T_TEXT,
            ],
        ],
    ];

    const EDGE_MAPPING = [
        'properties' => [
            'id'        => ['type' => self::T_KEYWORD],
            'type_id'   => ['type' => self::T_INT],
            'source_id' => ['type' => self::T_INT],
            'target_id' => ['type' => self::T_INT],
            'weight'    => ['type' => self::T_INT],
            'data'      => ['type' => self::T_OBJECT],
        ],
    ];

    const PORTAL_MAPPING = [
        '_parent'    => ['type' => self::O_USER],
        '_routing'   => ['required' => true],
        'properties' => [
            'id'                => ['type' => self::T_KEYWORD],
            'title'             => ['type' => self::T_KEYWORD] + self::ANALYZED,
            'status'            => ['type' => self::T_SHORT],
            'name'              => ['type' => self::T_KEYWORD],
            'version'           => ['type' => self::T_KEYWORD],
            'created'           => ['type' => self::T_DATE],
            'configuration'     => ['type' => self::T_OBJECT],
            'legacy'            => ['type' => self::T_INT],
            'logo'              => ['type' => self::T_TEXT],
            'score'             => ['type' => self::T_INT], # activity score
            'user_count'        => ['type' => self::T_INT],
            'active_user_count' => ['type' => self::T_INT], # last 30 days
            'plan'              => [
                'properties' => [
                    'name'     => ['type' => self::T_KEYWORD], # platform|premium
                    'status'   => ['type' => self::T_INT], # 0: Free, 1: Trial, 2: Paid, 3: Overdue invoice
                    'license'  => ['type' => self::T_INT],
                    'regional' => ['type' => self::T_KEYWORD],
                ],
            ],
            'csm'               => [
                'properties' => [
                    'user_id' => ['type' => self::T_INT],
                ],
            ],
        ],
    ];

    const CONFIGURATION_MAPPING = [
        '_parent'    => ['type' => self::O_PORTAL],
        '_routing'   => ['required' => true],
        'properties' => [
            'instance'  => ['type' => self::T_KEYWORD],
            'namespace' => ['type' => self::T_KEYWORD],
            'name'      => ['type' => self::T_KEYWORD],
            'public'    => ['type' => self::T_INT],
            'value'     => ['type' => self::T_OBJECT],
        ],
    ];

    const USER_MAPPING = [
        'properties' => [
            'id'           => ['type' => self::T_KEYWORD],
            'profile_id'   => ['type' => self::T_INT],
            'mail'         => ['type' => self::T_KEYWORD],
            'name'         => ['type' => self::T_KEYWORD] + self::ANALYZED,
            'first_name'   => ['type' => self::T_KEYWORD],
            'last_name'    => ['type' => self::T_KEYWORD],
            'created'      => ['type' => self::T_DATE],
            'login'        => ['type' => self::T_DATE],
            'access'       => ['type' => self::T_DATE],
            'status'       => ['type' => self::T_SHORT],
            'allow_public' => ['type' => self::T_INT],
            'avatar'       => ['type' => self::T_TEXT],
            'roles'        => ['type' => self::T_KEYWORD],
            'timestamp'    => ['type' => self::T_DATE],
        ],
    ];

    const ACCOUNT_MAPPING = [
        '_routing'          => ['required' => true],
        'properties'        => [
            'id'           => ['type' => self::T_KEYWORD],
            'instance'     => ['type' => self::T_KEYWORD],
            'mail'         => ['type' => self::T_KEYWORD] + self::ANALYZED,
            'name'         => ['type' => self::T_KEYWORD] + self::ANALYZED,
            'first_name'   => ['type' => self::T_KEYWORD] + self::ANALYZED,
            'last_name'    => ['type' => self::T_KEYWORD] + self::ANALYZED,
            'created'      => ['type' => self::T_DATE],
            'login'        => ['type' => self::T_DATE],
            'access'       => ['type' => self::T_DATE],
            'status'       => ['type' => self::T_SHORT],
            'allow_public' => ['type' => self::T_INT],
            'avatar'       => ['type' => self::T_TEXT],
            'roles'        => ['type' => self::T_KEYWORD],
            'groups'       => ['type' => self::T_KEYWORD] + self::ANALYZED,
            'timestamp'    => ['type' => self::T_DATE],
            'managers'     => ['type' => self::T_INT], # Use user.id of manager
            'metadata'     => [
                'properties' => [
                    'user_id'     => ['type' => self::T_INT],
                    'instance_id' => ['type' => self::T_INT],
                    'updated_at'  => ['type' => self::T_INT],
                ],
            ],
        ],
        'dynamic_templates' => [
            [
                'custom_field_string' => [
                    'path_match' => 'fields_*.*.value_string',
                    'mapping'    => ['type' => self::T_KEYWORD] + self::ANALYZED,
                ],
            ],
            [
                'custom_field_text' => [
                    'path_match' => 'fields_*.*.value_text',
                    'mapping'    => ['type' => self::T_TEXT],
                ],
            ],
            [
                'custom_field_integer' => [
                    'path_match' => 'fields_*.*.value_integer',
                    'mapping'    => ['type' => self::T_INT],
                ],
            ],
            [
                'custom_field_float' => [
                    'path_match' => 'fields_*.*.value_float',
                    'mapping'    => ['type' => self::T_DOUBLE],
                ],
            ],
            [
                'custom_field_date' => [
                    'path_match' => 'fields_*.*.value_date',
                    'mapping'    => ['type' => self::T_DATE],
                ],
            ],
            [
                'custom_field_datetime' => [
                    'path_match' => 'fields_*.*.value_datetime',
                    'mapping'    => ['type' => self::T_DATE],
                ],
            ],
        ],
    ];

    const LO_MAPPING = [
        '_routing'   => ['required' => true],
        'properties' => [
            'id'              => ['type' => self::T_KEYWORD],
            'type'            => ['type' => self::T_KEYWORD],
            'origin_id'       => ['type' => self::T_INT],
            'remote_id'       => ['type' => self::T_KEYWORD],
            'status'          => ['type' => self::T_SHORT],
            'private'         => ['type' => self::T_INT],
            'published'       => ['type' => self::T_INT],
            'marketplace'     => ['type' => self::T_INT],
            'sharing'         => ['type' => self::T_SHORT],
            'instance_id'     => ['type' => self::T_INT],
            'portal_name'     => ['type' => self::T_KEYWORD] + self::ANALYZED,
            'language'        => ['type' => self::T_KEYWORD],
            'locale'          => ['type' => self::T_KEYWORD],
            'title'           => ['type' => self::T_KEYWORD] + self::ANALYZED,
            'description'     => ['type' => self::T_TEXT],
            'tags'            => ['type' => self::T_KEYWORD] + self::ANALYZED,
            'custom_tags'     => ['type' => self::T_KEYWORD] + self::ANALYZED,
            'image'           => ['type' => self::T_TEXT],
            'quantity'        => ['type' => self::T_DOUBLE],
            'collection_id'   => ['type' => self::T_INT],
            'pricing'         => [
                'properties' => [
                    'currency'     => ['type' => self::T_KEYWORD],
                    'price'        => ['type' => self::T_DOUBLE],
                    'tax'          => ['type' => self::T_DOUBLE],
                    'tax_included' => ['type' => self::T_INT],
                    'tax_display'  => ['type' => self::T_INT],
                    'total'        => ['type' => self::T_DOUBLE],
                    'recurring'    => [
                        'properties' => [
                            'recurring' => ['type' => self::T_INT],
                            'interval'  => ['type' => self::T_TEXT],
                            'count'     => ['type' => self::T_INT],
                        ],
                    ],
                ],
            ],
            'duration'        => ['type' => self::T_INT], # Duration in minute
            'assessors'       => ['type' => self::T_INT],
            'allow_enrolment' => ['type' => self::T_INT],
            'totalEnrolment'  => ['type' => self::T_INT],
            'created'         => ['type' => self::T_DATE],
            'updated'         => ['type' => self::T_DATE],
            'fields'          => ['type' => self::T_OBJECT],
            'event'           => [
                'properties' => self::EVENT_PROPERTIES,
            ],
            'items_count'     => ['type' => self::T_INT], # Only count first child level
            'authors'         => [
                'type'       => self::T_NESTED,
                'properties' => self::USER_MAPPING['properties'],
            ],
            'group_ids'       => ['type' => self::T_INT],
            'data'            => [
                'properties' => [
                    'allow_resubmit' => ['type' => self::T_INT],
                    'allow_reenrol'  => ['type' => self::T_SHORT],
                    'label'          => ['type' => self::T_KEYWORD],
                    'pass_rate'      => ['type' => self::T_FLOAT],
                    'url'            => ['type' => self::T_TEXT],
                    'single_li'      => ['type' => self::T_SHORT],
                ],
            ],
            'locations'       => [
                'type'       => self::T_NESTED,
                'properties' => [
                    'id'                       => ['type' => self::T_KEYWORD],
                    'country'                  => ['type' => self::T_KEYWORD] + self::ANALYZED,
                    'country_name'             => ['type' => self::T_KEYWORD] + self::ANALYZED,
                    'administrative_area'      => ['type' => self::T_KEYWORD] + self::ANALYZED,
                    'administrative_area_name' => ['type' => self::T_KEYWORD] + self::ANALYZED,
                    'locality'                 => ['type' => self::T_KEYWORD] + self::ANALYZED,
                    'thoroughfare'             => ['type' => self::T_KEYWORD] + self::ANALYZED,
                    'coordinate'               => ['type' => self::T_GEO_POINT],
                ],
            ],
            'vote'            => [
                'properties' => [
                    'percent' => ['type' => self::T_INT],
                    'rank'    => ['type' => self::T_INT],
                    'like'    => ['type' => self::T_INT],
                    'dislike' => ['type' => self::T_INT],
                ],
            ],
            'metadata'        => [
                'properties' => [
                    'parents_authors_ids' => ['type' => self::T_INT],
                    'parents_id'          => ['type' => self::T_INT],
                    'instance_id'         => ['type' => self::T_INT],
                    'membership'          => ['type' => self::T_INT],
                    'updated_at'          => ['type' => self::T_INT],
                    'shared'              => ['type' => self::T_SHORT],
                    'shared_passive'      => ['type' => self::T_SHORT],
                    'customized'          => ['type' => self::T_SHORT],
                    'realm'               => ['type' => self::T_SHORT],
                ],
            ],
        ],
    ];

    const LO_GROUP_MAPPING = [
        '_parent'    => ['type' => self::O_LO],
        '_routing'   => ['required' => true],
        'properties' => [
            'lo_id'       => ['type' => self::T_INT],
            'instance_id' => ['type' => self::T_INT],
        ],
    ];

    const LO_TAG_MAPPING = [
        'properties' => [
            'title'    => ['type' => self::T_KEYWORD],
            'type'     => ['type' => self::T_KEYWORD],
            'metadata' => [
                'properties' => [
                    'instance_id' => ['type' => self::T_INT],
                ],
            ],
        ],
    ];

    const LO_POLICY_MAPPING = [
        '_parent'    => ['type' => self::O_LO],
        '_routing'   => ['required' => true],
        'properties' => [
            'id'                => ['type' => self::T_KEYWORD],
            'realm'             => ['type' => self::T_SHORT],
            'portal_id'         => ['type' => self::T_INT],
            'entity_type'       => ['type' => self::T_KEYWORD],
            'entity_id'         => ['type' => self::T_INT],
            # Attach group member ids to support explore learning object when share lo to group
            # Its value will be maintained by service #index-content-sharing
            'member_ids'        => ['type' => self::T_INT],
            'access_portal_ids' => ['type' => self::T_INT],
            'metadata'          => [
                'properties' => [
                    'instance_id' => ['type' => self::T_INT],
                ],
            ],
        ],
    ];

    const PLAN_MAPPING = [
        'properties' => [
            'id'          => ['type' => self::T_KEYWORD],
            'user_id'     => ['type' => self::T_INT],
            'assigner_id' => ['type' => self::T_INT],
            'entity_type' => ['type' => self::T_KEYWORD],
            'entity_id'   => ['type' => self::T_INT],
            'status'      => ['type' => self::T_SHORT],
            'created'     => ['type' => self::T_DATE],
            'due'         => ['type' => self::T_DATE],
            'data'        => ['type' => self::T_OBJECT],
            'metadata'    => [
                'properties' => [
                    'instance_id' => ['type' => self::T_INT],
                    'updated_at'  => ['type' => self::T_INT],
                ],
            ],
        ],
    ];

    /**
     * @TODO Make sure the revisions are indexed on content re-indexing.
     */
    const ENROLMENT_MAPPING = [
        '_parent'           => ['type' => self::O_LO],
        '_routing'          => ['required' => true],
        'properties'        => [
            'id'                  => ['type' => self::T_KEYWORD],
            'parent_enrolment_id' => ['type' => self::T_INT],
            // Type of enrolment: enrolment, manual-record, plan-assigned, award, award-item.
            'type'                => ['type' => self::T_KEYWORD],
            'profile_id'          => ['type' => self::T_INT],
            'lo_id'               => ['type' => self::T_INT],
            'parent_id'           => ['type' => self::T_INT],
            'status'              => ['type' => self::T_SHORT],
            'last_status'         => ['type' => self::T_SHORT],
            'quantity'            => ['type' => self::T_DOUBLE],
            'result'              => ['type' => self::T_INT],
            'pass'                => ['type' => self::T_INT],
            'assessors'           => ['type' => self::T_INT],
            // It's used to calculate scheduled duration: scheduled_duration = due_date - assigned_date;
            // To get it: assigned_date = plan.created;
            'assigned_date'       => ['type' => self::T_DATE],
            'start_date'          => ['type' => self::T_DATE],
            'end_date'            => ['type' => self::T_DATE],
            'due_date'            => ['type' => self::T_DATE],
            'submitted_date'      => ['type' => self::T_DATE],
            'marked_date'         => ['type' => self::T_DATE],
            // For award enrolment only
            'expire_date'         => ['type' => self::T_DATE],
            // It's used to calculate award completed duration: award_completed_duration = expire_date - begin_expire;
            // To get it: begin_expire = fixed expiry date ? start_date : end_date;
            'begin_expire'        => ['type' => self::T_DATE],
            'changed'             => ['type' => self::T_DATE],
            'created'             => ['type' => self::T_DATE],
            // Duration between end date and start date (hours).
            // @todo Support quiz and interactive.
            'duration'            => ['type' => self::T_INT],
            'is_assigned'         => ['type' => self::T_SHORT],
            'lo'                  => [
                'properties' => self::LO_MAPPING['properties'],
            ],
            'parent_lo'           => [
                'properties' => [
                    'id'    => ['type' => self::T_KEYWORD],
                    'type'  => ['type' => self::T_KEYWORD],
                    'title' => ['type' => self::T_KEYWORD] + self::ANALYZED,
                ],
            ],
            'assessor'            => [
                'properties' => [
                    'id'         => ['type' => self::T_KEYWORD],
                    'mail'       => ['type' => self::T_KEYWORD] + self::ANALYZED,
                    'name'       => ['type' => self::T_KEYWORD] + self::ANALYZED,
                    'first_name' => ['type' => self::T_KEYWORD] + self::ANALYZED,
                    'last_name'  => ['type' => self::T_KEYWORD] + self::ANALYZED,
                ],
            ],
            'account'             => [
                'properties' => self::ACCOUNT_MAPPING['properties'],
            ],
            'progress'            => [
                'properties' => [
                    EnrolmentStatuses::NOT_STARTED => ['type' => self::T_INT],
                    EnrolmentStatuses::IN_PROGRESS => ['type' => self::T_INT],
                    EnrolmentStatuses::COMPLETED   => ['type' => self::T_INT],
                    EnrolmentStatuses::EXPIRED     => ['type' => self::T_INT],
                    EnrolmentStatuses::PERCENTAGE  => ['type' => self::T_INT],
                ],
            ],
            'certificates'        => [
                'type'       => self::T_NESTED,
                'properties' => [
                    'type' => ['type' => self::T_KEYWORD],
                    'url'  => ['type' => self::T_TEXT],
                    'name' => ['type' => self::T_KEYWORD],
                    'size' => ['type' => self::T_TEXT],
                ],
            ],
            'metadata'            => [
                'properties' => [
                    'account_id'          => ['type' => self::T_INT],
                    'course_enrolment_id' => ['type' => self::T_INT],
                    'course_id'           => ['type' => self::T_INT],
                    'status'              => ['type' => self::T_SHORT],
                    'has_assessor'        => ['type' => self::T_SHORT],
                    'user_id'             => ['type' => self::T_INT],
                    'instance_id'         => ['type' => self::T_INT],
                    'updated_at'          => ['type' => self::T_INT],
                    'event_details'       => ['type' => self::T_KEYWORD] + self::ANALYZED, # Ex: Induction training | 08 August 2018 - 09 August 2018 | Brisbane
                ],
            ],
        ],
        'dynamic_templates' => [
            [
                'custom_field_string' => [
                    'path_match' => 'account.fields_*.*.value_string',
                    'mapping'    => ['type' => self::T_KEYWORD] + self::ANALYZED,
                ],
            ],
            [
                'custom_field_text' => [
                    'path_match' => 'account.fields_*.*.value_text',
                    'mapping'    => ['type' => self::T_TEXT],
                ],
            ],
            [
                'custom_field_integer' => [
                    'path_match' => 'account.fields_*.*.value_integer',
                    'mapping'    => ['type' => self::T_INT],
                ],
            ],
            [
                'custom_field_float' => [
                    'path_match' => 'account.fields_*.*.value_float',
                    'mapping'    => ['type' => self::T_DOUBLE],
                ],
            ],
            [
                'custom_field_date' => [
                    'path_match' => 'account.fields_*.*.value_date',
                    'mapping'    => ['type' => self::T_DATE],
                ],
            ],
            [
                'custom_field_datetime' => [
                    'path_match' => 'account.fields_*.*.value_datetime',
                    'mapping'    => ['type' => self::T_DATE],
                ],
            ],
        ],
    ];

    const ENROLMENT_MAPPING_REVISION = [
        '_routing'   => ['required' => true],
        '_parent'    => ['type' => self::O_ENROLMENT],
        'properties' => [
            'id'                  => ['type' => self::T_KEYWORD],
            'user_id'             => ['type' => self::T_INT],
            'portal_id'           => ['type' => self::T_INT],
            'lo_id'               => ['type' => self::T_INT],
            'parent_lo_id'        => ['type' => self::T_INT],
            'enrolment_id'        => ['type' => self::T_INT],
            'parent_enrolment_id' => ['type' => self::T_INT],
            'start_date'          => ['type' => self::T_DATE],
            'end_date'            => ['type' => self::T_DATE],
            'status'              => ['type' => self::T_SHORT],
            'result'              => ['type' => self::T_INT],
            'pass'                => ['type' => self::T_INT],
            'note'                => ['type' => self::T_TEXT],
            'timestamp'           => ['type' => self::T_DATE],
            'progress'            => [
                'properties' => [
                    EnrolmentStatuses::NOT_STARTED => ['type' => self::T_INT],
                    EnrolmentStatuses::IN_PROGRESS => ['type' => self::T_INT],
                    EnrolmentStatuses::COMPLETED   => ['type' => self::T_INT],
                    EnrolmentStatuses::EXPIRED     => ['type' => self::T_INT],
                    EnrolmentStatuses::PERCENTAGE  => ['type' => self::T_INT],
                ],
            ],
            'metadata'            => [
                'properties' => [
                    'updated_at'  => ['type' => self::T_INT],
                    'instance_id' => ['type' => self::T_INT],
                ],
            ],
        ],
    ];

    const ACCOUNT_ENROLMENT_MAPPING = [
        '_parent'    => ['type' => self::O_ACCOUNT],
        '_routing'   => ['required' => true],
        'properties' => [
            'id'       => ['type' => self::T_KEYWORD], # Enrolment ID
            'lo_id'    => ['type' => self::T_INT],
            'type'     => ['type' => self::T_KEYWORD],
            'status'   => ['type' => self::T_SHORT],
            'metadata' => [
                'properties' => [
                    'instance_id' => ['type' => self::T_INT],
                    'course_id'   => ['type' => self::T_INT],
                    'updated_at'  => ['type' => self::T_INT],
                ],
            ],
        ],
    ];

    const SUBMISSION_MAPPING = [
        '_parent'    => ['type' => self::O_ENROLMENT],
        '_routing'   => ['required' => true],
        'properties' => [
            'id'          => ['type' => self::T_KEYWORD],
            'revision_id' => ['type' => self::T_INT],
            'profile_id'  => ['type' => self::T_INT],
            'status'      => ['type' => self::T_SHORT],
            'created'     => ['type' => self::T_DATE],
            'updated'     => ['type' => self::T_DATE],
            'published'   => ['type' => self::T_INT],
            'assessors'   => ['type' => self::T_INT],
        ],
    ];

    const SUBMISSION_REVISION_MAPPING = [
        '_parent'    => ['type' => self::O_SUBMISSION],
        '_routing'   => ['required' => true],
        'properties' => [
            'id'      => ['type' => self::T_KEYWORD],
            'status'  => ['type' => self::T_SHORT],
            'created' => ['type' => self::T_DATE],
            'updated' => ['type' => self::T_DATE],
            'data'    => [
                'properties' => [
                    'files' => ['type' => self::T_OBJECT],
                ],
            ],
        ],
    ];

    const GROUP_MAPPING = [
        '_routing'   => ['required' => true],
        'properties' => [
            'id'          => ['type' => self::T_KEYWORD],
            'title'       => ['type' => self::T_KEYWORD] + self::ANALYZED,
            'portal_name' => ['type' => self::T_KEYWORD] + self::ANALYZED,
            'type'        => ['type' => self::T_KEYWORD],
            'description' => ['type' => self::T_TEXT],
            'image'       => ['type' => self::T_TEXT],
            'user_id'     => ['type' => self::T_INT],
            'visibility'  => ['type' => self::T_SHORT],
            'created'     => ['type' => self::T_DATE],
            'updated'     => ['type' => self::T_DATE],
            'metadata'    => [
                'properties' => [
                    'instance_id' => ['type' => self::T_INT],
                    'updated_at'  => ['type' => self::T_INT],
                ],
            ],
        ],
    ];

    const GROUP_ITEM_MAPPING = [
        '_routing'   => ['required' => true],
        '_parent'    => ['type' => self::O_GROUP],
        'properties' => [
            'id'          => ['type' => self::T_KEYWORD],
            'entity_type' => ['type' => self::T_KEYWORD],
            'entity_id'   => ['type' => self::T_INT],
            'status'      => ['type' => self::T_SHORT],
            'metadata'    => [
                'properties' => [
                    'instance_id' => ['type' => self::T_INT],
                    'updated_at'  => ['type' => self::T_INT],
                ],
            ],
        ],
    ];

    const MAIL_MAPPING = [
        '_parent'    => ['type' => self::O_PORTAL],
        '_routing'   => ['required' => true],
        'properties' => [
            'id'          => ['type' => self::T_KEYWORD],
            'recipient'   => ['type' => self::T_KEYWORD],
            'sender'      => ['type' => self::T_KEYWORD],
            'cc'          => ['type' => self::T_KEYWORD],
            'bcc'         => ['type' => self::T_KEYWORD],
            'subject'     => ['type' => self::T_KEYWORD],
            'body'        => ['type' => self::T_TEXT],
            'html'        => ['type' => self::T_TEXT],
            'context'     => ['type' => self::T_OBJECT],
            'options'     => ['type' => self::T_OBJECT],
            'attachments' => ['type' => self::T_OBJECT],
            'timestamp'   => ['type' => self::T_DATE],
        ],
    ];

    const PAYMENT_TRANSACTION_MAPPING = [
        'properties' => [
            'id'                 => ['type' => self::T_KEYWORD],
            'instance_id'        => ['type' => self::T_INT],
            'local_id'           => ['type' => self::T_INT],
            'email'              => ['type' => self::T_KEYWORD],
            'status'             => ['type' => self::T_SHORT],
            'amount'             => ['type' => self::T_DOUBLE],
            'currency'           => ['type' => self::T_KEYWORD],
            'created'            => ['type' => self::T_DATE],
            'updated'            => ['type' => self::T_DATE],
            'payment_method'     => ['type' => self::T_KEYWORD],
            'premium_purchase'   => ['type' => self::T_INT],
            'user_id'            => ['type' => self::T_INT],
            'user'               => [
                'properties' => self::USER_MAPPING['properties'],
            ],
            'items'              => [
                'type'       => self::T_NESTED,
                'properties' => self::PAYMENT_TRANSACTION_ITEM_MAPPING['properties'],
            ],
            'credit_usage_count' => ['type' => self::T_INT],
        ],
    ];

    const PAYMENT_TRANSACTION_ITEM_MAPPING = [
        'properties' => [
            'id'                   => ['type' => self::T_KEYWORD],
            'product_type'         => ['type' => self::T_KEYWORD],
            'product_id'           => ['type' => self::T_INT],
            'product_title'        => ['type' => self::T_KEYWORD] + self::ANALYZED,
            'product_parent_id'    => ['type' => self::T_INT],
            'product_parent_title' => ['type' => self::T_KEYWORD] + self::ANALYZED,
            'product_coupon_code'  => ['type' => self::T_KEYWORD] + self::ANALYZED,
            'qty'                  => ['type' => self::T_INT],
            'price'                => ['type' => self::T_DOUBLE],
            'tax'                  => ['type' => self::T_DOUBLE],
            'tax_included'         => ['type' => self::T_INT],
        ],
    ];

    const QUIZ_USER_ANSWER_MAPPING = [
        'properties' => [
            'id'            => ['type' => self::T_KEYWORD],
            'question_type' => ['type' => self::T_KEYWORD],
            'answer'        => ['type' => self::T_TEXT],
            'created'       => ['type' => self::T_DATE],
            'updated'       => ['type' => self::T_DATE],
            'is_correct'    => ['type' => self::T_INT],
            'is_skipped'    => ['type' => self::T_INT],
            'is_evaluated'  => ['type' => self::T_INT],
            'points'        => ['type' => self::T_INT],
            // @todo Handle updating question.
            'question'      => ['type' => self::T_KEYWORD] + self::ANALYZED,
            'counter'       => ['type' => self::T_INT],
            'user'          => [
                'properties' => self::USER_MAPPING['properties'],
            ],
            'li'            => [
                'properties' => self::LO_MAPPING['properties'],
            ],
            'course'        => [
                'properties' => self::LO_MAPPING['properties'],
            ],
            'metadata'      => [
                'properties' => [
                    'li_id'     => ['type' => self::T_INT],
                    'course_id' => ['type' => self::T_INT],
                    'user_id'   => ['type' => self::T_INT],
                ],
            ],
            'title'         => ['type' => self::T_KEYWORD] + self::ANALYZED,
        ],
    ];

    const PURCHASE_REQUEST_MAPPING = [
        'properties' => [
            'id'            => ['type' => self::T_KEYWORD],
            'portal_id'     => ['type' => self::T_INT],
            'user'          => [
                'properties' => self::USER_MAPPING['properties'],
            ],
            'manager'       => [
                'properties' => self::USER_MAPPING['properties'],
            ],
            'lo'            => [
                'properties' => self::LO_MAPPING['properties'],
            ],
            'status'        => ['type' => self::T_SHORT],
            'request_date'  => ['type' => self::T_DATE],
            'response_date' => ['type' => self::T_DATE],
            'approve_url'   => ['type' => self::T_TEXT],
            'reject_url'    => ['type' => self::T_TEXT],
            'metadata'      => [
                'properties' => [
                    'user_id'    => ['type' => self::T_INT],
                    'manager_id' => ['type' => self::T_INT],
                    'lo_id'      => ['type' => self::T_INT],
                ],
            ],
        ],
    ];

    const ECK_METADATA_MAPPING = [
        '_routing'   => ['required' => true],
        'properties' => [
            'instance'    => ['type' => self::T_KEYWORD],
            'entity_type' => ['type' => self::T_KEYWORD],
            'field'       => [
                'type'       => self::T_NESTED,
                'properties' => [
                    'id'           => ['type' => self::T_KEYWORD],
                    'name'         => ['type' => self::T_KEYWORD],
                    'description'  => ['type' => self::T_TEXT],
                    'label'        => ['type' => self::T_KEYWORD],
                    'help'         => ['type' => self::T_KEYWORD],
                    'type'         => ['type' => self::T_KEYWORD],
                    'published'    => ['type' => self::T_INT],
                    'weight'       => ['type' => self::T_INT],
                    'max_rows'     => ['type' => self::T_INT],
                    'parent_field' => ['type' => self::T_KEYWORD],
                    'data'         => ['type' => self::T_OBJECT],
                    'metadata'     => [
                        'properties' => [
                            'instance_id' => ['type' => self::T_INT],
                            'updated_at'  => ['type' => self::T_INT],
                        ],
                    ],
                ],
            ],
        ],
    ];

    const COUPON_MAPPING = [
        '_routing'   => ['required' => true],
        'properties' => [
            'id'           => ['type' => self::T_KEYWORD],
            'title'        => ['type' => self::T_KEYWORD] + self::ANALYZED,
            'code'         => ['type' => self::T_KEYWORD],
            'instance_id'  => ['type' => self::T_INT],
            'user_id'      => ['type' => self::T_INT],
            'coupon_type'  => ['type' => self::T_SHORT],
            'coupon_value' => ['type' => self::T_DOUBLE],
            'status'       => ['type' => self::T_SHORT],
            'limitation'   => ['type' => self::T_SHORT],
            'expiration'   => ['type' => self::T_DATE],
            'created'      => ['type' => self::T_DATE],
            'updated'      => ['type' => self::T_DATE],
            'usage_count'  => ['type' => self::T_INT],
            'usage'        => [
                'type'       => self::T_NESTED,
                'properties' => [
                    'user_id'        => ['type' => self::T_INT],
                    'transaction_id' => ['type' => self::T_INT],
                    'created'        => ['type' => self::T_DATE],
                ],
            ],
            'items'        => [
                'type'       => self::T_NESTED,
                'properties' => self::LO_MAPPING['properties'],
            ],
            'metadata'     => [
                'properties' => [
                    'instance_id' => ['type' => self::T_INT],
                    'updated_at'  => ['type' => self::T_INT],
                ],
            ],
        ],
    ];

    const CREDIT_MAPPING = [
        '_routing'   => ['required' => true],
        '_parent'    => ['type' => self::O_PAYMENT_TRANSACTION],
        'properties' => [
            'user'      => [
                'properties' => self::USER_MAPPING['properties'],
            ],
            'lo'        => [
                'properties' => self::LO_MAPPING['properties'],
            ],
            'portal_id' => ['type' => self::T_INT],
            'total'     => ['type' => self::T_INT],
            'used'      => ['type' => self::T_INT],
            'remaining' => ['type' => self::T_INT],
            'metadata'  => [
                'properties' => [
                    'user_id'     => ['type' => self::T_INT],
                    'lo_id'       => ['type' => self::T_INT],
                    'instance_id' => ['type' => self::T_INT],
                    'updated_at'  => ['type' => self::T_INT],
                ],
            ],
        ],
    ];

    const INSTRUCTOR_PROPERTIES = [
        'id'         => ['type' => self::T_KEYWORD],
        'profile_id' => ['type' => self::T_INT],
        'instance'   => ['type' => self::T_KEYWORD],
        'mail'       => ['type' => self::T_KEYWORD] + self::ANALYZED,
        'name'       => ['type' => self::T_KEYWORD] + self::ANALYZED,
        'first_name' => ['type' => self::T_KEYWORD] + self::ANALYZED,
        'last_name'  => ['type' => self::T_KEYWORD] + self::ANALYZED,
        'status'     => ['type' => self::T_SHORT],
        'avatar'     => ['type' => self::T_TEXT],
        'roles'      => ['type' => self::T_KEYWORD],
    ];

    const EVENT_PROPERTIES = [
        'id'                       => ['type' => self::T_KEYWORD],
        'lo_id'                    => ['type' => self::T_INT],
        'title'                    => ['type' => self::T_KEYWORD] + self::ANALYZED,
        'start'                    => ['type' => self::T_DATE],
        'end'                      => ['type' => self::T_DATE],
        'timezone'                 => ['type' => self::T_KEYWORD],
        'seats'                    => ['type' => self::T_INT], # Or attendee_limit
        'available_seats'          => ['type' => self::T_INT],
        'country'                  => ['type' => self::T_KEYWORD],
        'country_name'             => ['type' => self::T_KEYWORD] + self::ANALYZED,
        'administrative_area'      => ['type' => self::T_KEYWORD],
        'administrative_area_name' => ['type' => self::T_KEYWORD] + self::ANALYZED,
        'sub_administrative_area'  => ['type' => self::T_KEYWORD],
        'locality'                 => ['type' => self::T_KEYWORD],
        'dependent_locality'       => ['type' => self::T_KEYWORD],
        'thoroughfare'             => ['type' => self::T_KEYWORD] + self::ANALYZED,
        'premise'                  => ['type' => self::T_KEYWORD],
        'sub_premise'              => ['type' => self::T_KEYWORD],
        'organisation_name'        => ['type' => self::T_KEYWORD],
        'name_line'                => ['type' => self::T_KEYWORD],
        'postal_code'              => ['type' => self::T_KEYWORD],
        'location_name'            => ['type' => self::T_KEYWORD] + self::ANALYZED,
        'module_title'             => ['type' => self::T_KEYWORD] + self::ANALYZED,
        'instructor_ids'           => ['type' => self::T_INT],
        'instructors'              => [
            'type'       => self::T_NESTED,
            'properties' => self::INSTRUCTOR_PROPERTIES,
        ],
        'coordinate'               => ['type' => self::T_GEO_POINT],
    ];

    const EVENT_MAPPING = [
        '_routing'   => ['required' => true],
        '_parent'    => ['type' => self::O_LO],
        'properties' => self::EVENT_PROPERTIES + [
                'parent'   => [
                    'properties' => self::LO_MAPPING['properties'],
                ],
                'metadata' => [
                    'properties' => [
                        'instance_id' => ['type' => self::T_INT],
                        'updated_at'  => ['type' => self::T_INT],
                    ],
                ],
            ],
    ];

    const EVENT_ATTENDANCE_PROPERTIES = [
        'id'           => ['type' => self::T_KEYWORD],
        'user_id'      => ['type' => self::T_INT],
        'lo_id'        => ['type' => self::T_INT],
        'enrolment_id' => ['type' => self::T_INT],
        'event_id'     => ['type' => self::T_INT],
        'portal_id'    => ['type' => self::T_INT],
        'profile_id'   => ['type' => self::T_INT],
        'start_at'     => ['type' => self::T_DATE],
        'end_at'       => ['type' => self::T_DATE],
        'status'       => ['type' => self::T_SHORT],
        'result'       => ['type' => self::T_INT],
        'pass'         => ['type' => self::T_INT],
        'timestamp'    => ['type' => self::T_DATE],
        'progress'     => [
            'properties' => [
                AttendanceStatuses::ATTENDED     => ['type' => self::T_INT],
                AttendanceStatuses::NOT_ATTENDED => ['type' => self::T_INT],
                AttendanceStatuses::ATTENDING    => ['type' => self::T_INT],
                AttendanceStatuses::PENDING      => ['type' => self::T_INT],
            ],
        ],
    ];

    const EVENT_ATTENDANCE_MAPPING = [
        '_parent'    => ['type' => self::O_ENROLMENT],
        '_routing'   => ['required' => true],
        'properties' => self::EVENT_PROPERTIES + [
                'metadata' => [
                    'properties' => [
                        'instance_id' => ['type' => self::T_INT],
                        'updated_at'  => ['type' => self::T_INT],
                    ],
                ],
            ],
    ];

    const AWARD_MAPPING = [
        '_routing'   => ['required' => true],
        'properties' => [
            'id'          => ['type' => self::T_KEYWORD],
            'revision_id' => ['type' => self::T_INT],
            'title'       => ['type' => self::T_KEYWORD],
            'description' => ['type' => self::T_TEXT],
            'image'       => ['type' => self::T_TEXT],
            'user_id'     => ['type' => self::T_INT],
            'instance_id' => ['type' => self::T_INT],
            'published'   => ['type' => self::T_INT],
            'quantity'    => ['type' => self::T_DOUBLE],
            // Save as keyword, not date, because there are dynamic values (e.g.
            // +6 day, +2 month). UI will render its way.
            'expire'      => ['type' => self::T_KEYWORD],
            'created'     => ['type' => self::T_DATE],
            'items_count' => ['type' => self::T_INT],
            'tags'        => ['type' => self::T_KEYWORD] + self::ANALYZED,
            'locale'      => ['type' => self::T_KEYWORD],
            'metadata'    => [
                'properties' => [
                    'instance_id' => ['type' => self::T_INT],
                    'updated_at'  => ['type' => self::T_INT],
                ],
            ],
        ],
    ];

    const AWARD_ITEM_MAPPING = [
        '_parent'    => ['type' => self::O_AWARD],
        '_routing'   => ['required' => true],
        'properties' => [
            'id'          => ['type' => self::T_KEYWORD],
            'entity_id'   => ['type' => self::T_INT],
            'title'       => ['type' => self::T_KEYWORD] + self::ANALYZED,
            'description' => ['type' => self::T_TEXT],
            'type'        => ['type' => self::T_KEYWORD],
            'quantity'    => ['type' => self::T_DOUBLE],
            'weight'      => ['type' => self::T_INT],
            'metadata'    => [
                'properties' => [
                    'award_revision_id' => ['type' => self::T_INT],
                ],
            ],
        ],
    ];

    const AWARD_ITEM_MANUAL_MAPPING = [
        '_parent'    => ['type' => self::O_AWARD],
        '_routing'   => ['required' => true],
        'properties' => [
            'id'              => ['type' => self::T_KEYWORD],
            'entity_id'       => ['type' => self::T_INT],
            'title'           => ['type' => self::T_KEYWORD] + self::ANALYZED,
            'description'     => ['type' => self::T_TEXT],
            'type'            => ['type' => self::T_KEYWORD],
            'quantity'        => ['type' => self::T_DOUBLE],
            'completion_date' => ['type' => self::T_DATE],
            'certificate'     => ['type' => self::T_OBJECT],
            'verified'        => ['type' => self::T_INT],
            'pass'            => ['type' => self::T_INT],
            'weight'          => ['type' => self::T_INT],
            'categories'      => ['type' => self::T_KEYWORD] + self::ANALYZED,
        ],
    ];

    const AWARD_ACHIEVEMENT_MAPPING = [
        '_parent'    => ['type' => self::O_AWARD_ENROLMENT],
        '_routing'   => ['required' => true],
        'properties' => [
            'id'            => ['type' => self::T_KEYWORD],
            'award_item_id' => ['type' => self::T_INT],
            'quantity'      => ['type' => self::T_DOUBLE],
            'created'       => ['type' => self::T_DATE],
        ],
    ];

    const ACTIVITY_MAPPING = [
        '_routing'   => ['required' => true],
        'properties' => [
            'id'          => ['type' => self::T_KEYWORD],
            'instance_id' => ['type' => self::T_INT],
            'actor_id'    => ['type' => self::T_INT],
            'user_id'     => ['type' => self::T_INT],
            'entity_type' => ['type' => self::T_KEYWORD],
            'entity_id'   => ['type' => self::T_INT],
            'action_id'   => ['type' => self::T_INT],
            'tags'        => ['type' => self::T_KEYWORD] + self::ANALYZED,
            'created'     => ['type' => self::T_DATE],
            'updated'     => ['type' => self::T_DATE],
            'context'     => [
                'properties' => [
                    'actor'  => ['type' => self::T_KEYWORD],
                    'user'   => ['type' => self::T_KEYWORD],
                    'entity' => [
                        'properties' => [
                            'title' => ['type' => self::T_KEYWORD],
                            'type'  => ['type' => self::T_KEYWORD],
                        ],
                    ],
                    'diff'   => [
                        'properties' => [
                            'diff_field_name' => ['type' => self::T_TEXT], # Can not use `field` because it already used
                            'old'             => ['type' => self::T_TEXT],
                            'new'             => ['type' => self::T_TEXT],
                        ],
                    ],
                    'target' => [
                        'properties' => [
                            'id'    => ['type' => self::T_KEYWORD],
                            'title' => ['type' => self::T_KEYWORD],
                            'type'  => ['type' => self::T_KEYWORD],
                        ],
                    ],
                ],
            ],
        ],
    ];

    const SUGGESTION_CATEGORY_MAPPING = [
        '_routing'   => ['required' => true],
        'properties' => [
            'category' => [
                'type'                         => self::T_COMPLETION,
                'analyzer'                     => self::A_WHITESPACE,
                'preserve_separators'          => true,
                'preserve_position_increments' => true,
                'max_input_length'             => self::MAX_INPUT_LENGTH,
                'contexts'                     => [
                    [
                        'name' => 'instance_id',
                        'type' => self::T_COMPLETION_CATEGORY,
                    ],
                ],
            ],
            'metadata' => [
                'properties' => [
                    'instance_id' => ['type' => self::T_INT],
                ],
            ],
        ],
    ];

    const SUGGESTION_TAG_MAPPING = [
        '_routing'   => ['required' => true],
        'properties' => [
            'tag'      => [
                'type'                         => self::T_COMPLETION,
                'analyzer'                     => self::A_WHITESPACE,
                'preserve_separators'          => true,
                'preserve_position_increments' => true,
                'max_input_length'             => self::MAX_INPUT_LENGTH,
                'contexts'                     => [
                    [
                        // since suggestion query only supports context
                        // we also need to index instance_id here
                        'name' => 'instance_id',
                        'type' => self::T_COMPLETION_CATEGORY,
                    ],
                ],
            ],
            'metadata' => [
                'properties' => [
                    'instance_id' => ['type' => self::T_INT],
                ],
            ],
        ],
    ];

    const MY_TEAM_MAPPING = [
        'properties' => [
            # Data for filters.
            'accessor_id'         => ['type' => self::T_INT],
            'account'             => [
                'properties' => [
                    'id'         => ['type' => self::T_KEYWORD],
                    'mail'       => ['type' => self::T_KEYWORD],
                    'first_name' => ['type' => self::T_KEYWORD],
                    'last_name'  => ['type' => self::T_KEYWORD],
                    'avatar'     => ['type' => self::T_TEXT],
                    'status'     => ['type' => self::T_SHORT],
                ],
            ],
            # Real properties for reporting
            'role'                => ['type' => self::T_INT],
            'count_enrolment'     => ['type' => self::T_INT],
            'count_upcoming'      => ['type' => self::T_INT],
            'count_overdue'       => ['type' => self::T_INT],
            'count_mark'          => ['type' => self::T_INT],
            'has_child'           => ['type' => self::T_INT],
            'has_myteam_progress' => ['type' => self::T_INT],
        ],
    ];

    const CONTRACT_MAPPING = [
        'properties' => [
            'id'              => ['type' => self::T_KEYWORD],
            'name'            => ['type' => self::T_KEYWORD],
            'instance_id'     => ['type' => self::T_INT],
            'parent_id'       => ['type' => self::T_INT],
            'csm_id'          => ['type' => self::T_INT],
            'portal'          => ['type' => self::T_KEYWORD],
            'user_id'         => ['type' => self::T_INT],
            'staff_id'        => ['type' => self::T_INT],
            'staff'           => [
                'properties' => self::USER_MAPPING['properties'],
            ],
            'number_users'    => ['type' => self::T_INT],
            'price'           => ['type' => self::T_DOUBLE],
            'currency'        => ['type' => self::T_KEYWORD],
            'aud_net_amount'  => ['type' => self::T_FLOAT],
            'status'          => ['type' => self::T_SHORT],
            'start_date'      => ['type' => self::T_DATE],
            'signed_date'     => ['type' => self::T_DATE],
            'initial_term'    => ['type' => self::T_KEYWORD],
            'tax'             => ['type' => self::T_DOUBLE],
            'frequency'       => ['type' => self::T_KEYWORD],
            'frequency_other' => ['type' => self::T_KEYWORD],
            'custom_term'     => ['type' => self::T_TEXT],
            'payment_method'  => ['type' => self::T_KEYWORD],
            'renewal_date'    => ['type' => self::T_DATE],
            'cancel_date'     => ['type' => self::T_DATE],
            'created'         => ['type' => self::T_DATE],
            'updated'         => ['type' => self::T_DATE],
        ],
    ];

    const METRIC_MAPPING = [
        'properties' => [
            'id'           => ['type' => self::T_KEYWORD],
            'title'        => ['type' => self::T_KEYWORD],
            'user'         => [
                'properties' => self::USER_MAPPING['properties'],
            ],
            'type'         => ['type' => self::T_KEYWORD],
            'metric_value' => ['type' => self::T_DOUBLE],
            'status'       => ['type' => self::T_SHORT],
            'start_date'   => ['type' => self::T_DATE],
            'description'  => ['type' => self::T_TEXT],
            'created'      => ['type' => self::T_DATE],
            'updated'      => ['type' => self::T_DATE],
            'metadata'     => [
                'properties' => [
                    'user_id' => ['type' => self::T_INT],
                ],
            ],
        ],
    ];

    const LO_COLLECTION_MAPPING = [
        '_parent'    => ['type' => self::O_LO],
        '_routing'   => ['required' => true],
        'properties' => [
            'lo_id'         => ['type' => self::T_INT],
            'collection_id' => ['type' => self::T_INT],
        ],
    ];

    public static function portalIndex(int $portalId)
    {
        return static::INDEX . '_portal_' . $portalId;
    }
}
