<?php

namespace go1\util\es;

/**
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/mapping-types.html
 */
class Schema
{
    const INDEX = ES_INDEX;
    const TEMP  = -32;

    const T_BOOL    = 'boolean';
    const T_SHORT   = 'short';
    const T_INT     = 'integer';
    const T_FLOAT   = 'float';
    const T_TEXT    = 'text';
    const T_KEYWORD = 'keyword';
    const T_DATE    = 'date';
    const T_ARRAY   = 'array';
    const T_OBJECT  = 'object';
    const T_NESTED  = 'nested';

    const O_EDGE                    = 'edge';
    const O_PORTAL                  = 'portal';
    const O_CONFIG                  = 'configuration';
    const O_USER                    = 'user';
    const O_ACCOUNT                 = 'account';
    const O_LO                      = 'lo';
    const O_PLAN                    = 'plan';
    const O_ENROLMENT               = 'enrolment';
    const O_ASSIGNMENT              = 'asm_assignment';
    const O_SUBMISSION              = 'asm_submission';
    const O_SUBMISSION_REVISION     = 'asm_submission_revision';
    const O_GROUP                   = 'group';
    const O_MAIL                    = 'mail';

    const SCHEMA = [
        'index' => self::INDEX,
        'body'  => self::BODY,
    ];

    const BODY = [
        // 'settings' => [],
        'mappings' => self::MAPPING,
    ];

    const MAPPING = [
        self::O_EDGE                    => self::EDGE_MAPPING,
        self::O_PORTAL                  => self::PORTAL_MAPPING,
        self::O_CONFIG                  => self::CONFIGURATION_MAPPING,
        self::O_USER                    => self::USER_MAPPING,
        self::O_ACCOUNT                 => self::ACCOUNT_MAPPING,
        self::O_LO                      => self::LO_MAPPING,
        self::O_PLAN                    => self::PLAN_MAPPING,
        self::O_ENROLMENT               => self::ENROLMENT_MAPPING,
        self::O_ASSIGNMENT              => self::ASSIGNMENT_MAPPING,
        self::O_SUBMISSION              => self::SUBMISSION_MAPPING,
        self::O_SUBMISSION_REVISION     => self::SUBMISSION_REVISION_MAPPING,
        self::O_GROUP                   => self::GROUP_MAPPING,
        self::O_MAIL                    => self::MAIL_MAPPING,
    ];

    const ANALYZED = [
        'fields' => [
            'analyzed' => [
                'type' => self::T_TEXT
            ]
        ],
    ];

    const EDGE_MAPPING = [
        //'_source'    => ['enabled' => true],
        'properties' => [
            'id'        => ['type' => self::T_INT],
            'type_id'   => ['type' => self::T_INT],
            'source_id' => ['type' => self::T_INT],
            'target_id' => ['type' => self::T_INT],
            'weight'    => ['type' => self::T_INT],
            'data'      => ['type' => self::T_OBJECT],
        ],
    ];

    const PORTAL_MAPPING = [
        //'_source'    => ['enabled' => true],
        '_parent'    => ['type' => self::O_USER],
        '_routing'   => ['required' => true],
        'properties' => [
            'id'            => ['type' => self::T_INT],
            'title'         => ['type' => self::T_KEYWORD] + self::ANALYZED,
            'status'        => ['type' => self::T_SHORT],
            'version'       => ['type' => self::T_KEYWORD],
            'created'       => ['type' => self::T_DATE],
            'configuration' => ['type' => self::T_OBJECT],
        ],
    ];

    const CONFIGURATION_MAPPING = [
        //'_source'    => ['enabled' => true],
        '_parent'    => ['type' => self::O_PORTAL],
        '_routing'   => ['required' => true],
        'properties' => [
            'instance'  => ['type' => self::T_KEYWORD],
            'namespace' => ['type' => self::T_KEYWORD],
            'name'      => ['type' => self::T_KEYWORD],
            'public'    => ['type' => self::T_BOOL],
            'data'      => ['type' => self::T_OBJECT],
        ],
    ];

    const USER_MAPPING = [
        //'_source'    => ['enabled' => true],
        'properties' => [
            'id'           => ['type' => self::T_INT],
            'profile_id'   => ['type' => self::T_INT],
            'mail'         => ['type' => self::T_KEYWORD],
            'name'         => ['type' => self::T_KEYWORD],
            'first_name'   => ['type' => self::T_KEYWORD],
            'last_name'    => ['type' => self::T_KEYWORD],
            'created'      => ['type' => self::T_DATE],
            'login'        => ['type' => self::T_DATE],
            'access'       => ['type' => self::T_DATE],
            'status'       => ['type' => self::T_SHORT],
            'allow_public' => ['type' => self::T_BOOL],
            'avatar'       => ['type' => self::T_TEXT],
            'roles'        => ['type' => self::T_KEYWORD],
        ],
    ];

    const ACCOUNT_MAPPING = [
        //'_source'    => ['enabled' => true],
        '_parent'    => ['type' => self::O_USER],
        '_routing'   => ['required' => true],
        'properties' => [
            'id'           => ['type' => self::T_INT],
            'instance'     => ['type' => self::T_KEYWORD],
            'mail'         => ['type' => self::T_KEYWORD] + self::ANALYZED,
            'name'         => ['type' => self::T_KEYWORD] + self::ANALYZED,
            'first_name'   => ['type' => self::T_KEYWORD] + self::ANALYZED,
            'last_name'    => ['type' => self::T_KEYWORD] + self::ANALYZED,
            'created'      => ['type' => self::T_DATE],
            'access'       => ['type' => self::T_DATE],
            'status'       => ['type' => self::T_SHORT],
            'allow_public' => ['type' => self::T_BOOL],
            'roles'        => ['type' => self::T_KEYWORD],
            'fields'       => ['type' => self::T_OBJECT],
        ],
    ];

    const LO_MAPPING = [
        //'_source'    => ['enabled' => true],
        '_parent'    => ['type' => self::O_PORTAL],
        '_routing'   => ['required' => true],
        'properties' => [
            'id'          => ['type' => self::T_INT],
            'type'        => ['type' => self::T_KEYWORD],
            'origin_id'   => ['type' => self::T_INT],
            'status'      => ['type' => self::T_SHORT],
            'private'     => ['type' => self::T_BOOL],
            'published'   => ['type' => self::T_BOOL],
            'marketplace' => ['type' => self::T_BOOL],
            'sharing'     => ['type' => self::T_SHORT],
            'instance_id' => ['type' => self::T_INT],
            'language'    => ['type' => self::T_KEYWORD],
            'locale'      => ['type' => self::T_KEYWORD],
            'title'       => ['type' => self::T_KEYWORD] + self::ANALYZED,
            'description' => ['type' => self::T_TEXT],
            'tags'        => ['type' => self::T_KEYWORD] + self::ANALYZED,
            'image'       => ['type' => self::T_TEXT],
            'pricing'     => [
                'type'       => self::T_NESTED,
                'properties' => [
                    'currency'     => ['type' => self::T_KEYWORD],
                    'price'        => ['type' => self::T_FLOAT],
                    'tax'          => ['type' => self::T_FLOAT],
                    'tax_included' => ['type' => self::T_BOOL],
                ],
            ],
            'duration'    => ['type' => self::T_INT], # Duration in minute
            'assessors'   => ['type' => self::T_INT],
            'created'     => ['type' => self::T_DATE],
            'updated'     => ['type' => self::T_DATE],
            'fields'      => ['type' => self::T_OBJECT],
        ],
    ];

    const PLAN_MAPPING = [
        //'_source'    => ['enabled' => true],
        'properties' => [
            'id'          => ['type' => self::T_INT],
            'user_id'     => ['type' => self::T_INT],
            'assigner_id' => ['type' => self::T_INT],
            'entity_type' => ['type' => self::T_KEYWORD],
            'entity_id'   => ['type' => self::T_INT],
            'status'      => ['type' => self::T_SHORT],
            'created'     => ['type' => self::T_DATE],
            'due'         => ['type' => self::T_DATE],
            'data'        => ['type' => self::T_OBJECT],
        ],
    ];

    /**
     * @TODO Make sure the revisions are indexed on content re-indexing.
     */
    const ENROLMENT_MAPPING = [
        //'_source'    => ['enabled' => true],
        '_parent'    => ['type' => self::O_LO],
        '_routing'   => ['required' => true],
        'properties' => [
            'id'         => ['type' => self::T_INT],
            'profile_id' => ['type' => self::T_INT],
            'lo_id'      => ['type' => self::T_INT],
            'parent_id'  => ['type' => self::T_INT],
            'status'     => ['type' => self::T_SHORT],
            'result'     => ['type' => self::T_INT],
            'pass'       => ['type' => self::T_BOOL],
            'assessors'  => ['type' => self::T_INT],
            'start_date' => ['type' => self::T_DATE],
            'end_date'   => ['type' => self::T_DATE],
            'changed'    => ['type' => self::T_DATE],
        ],
    ];

    const ASSIGNMENT_MAPPING = [
        //'_source'    => ['enabled' => true],
        '_parent'    => ['type' => self::O_LO],
        '_routing'   => ['required' => true],
        'properties' => [
            'id'          => ['type' => self::T_INT],
            'user_id'     => ['type' => self::T_INT],
            'module_id'   => ['type' => self::T_INT],
            'created'     => ['type' => self::T_DATE],
            'updated'     => ['type' => self::T_DATE],
            'published'   => ['type' => self::T_BOOL],
            'title'       => ['type' => self::T_KEYWORD],
            'description' => ['type' => self::T_TEXT],
            'data'        => ['type' => self::T_OBJECT],
        ],
    ];

    const SUBMISSION_MAPPING = [
        //'_source'    => ['enabled' => true],
        '_parent'    => ['type' => self::O_ASSIGNMENT],
        '_routing'   => ['required' => true],
        'properties' => [
            'id'            => ['type' => self::T_INT],
            'revision_id'   => ['type' => self::T_INT],
            'profile_id'    => ['type' => self::T_INT],
            'status'        => ['type' => self::T_SHORT],
            'created'       => ['type' => self::T_DATE],
            'updated'       => ['type' => self::T_DATE],
            'published'     => ['type' => self::T_BOOL],
            'assessors'     => ['type' => self::T_INT],
        ],
    ];

    const SUBMISSION_REVISION_MAPPING = [
        //'_source'    => ['enabled' => true],
        '_parent'    => ['type' => self::O_SUBMISSION],
        '_routing'   => ['required' => true],
        'properties' => [
            'id'            => ['type' => self::T_INT],
            'status'        => ['type' => self::T_SHORT],
            'created'       => ['type' => self::T_DATE],
            'updated'       => ['type' => self::T_DATE],
            'data'          => ['type' => self::T_OBJECT],
        ],
    ];

    const GROUP_MAPPING = [
        //'_source'    => ['enabled' => true],
        '_parent'    => ['type' => self::O_PORTAL],
        '_routing'   => ['required' => true],
        'properties' => [
            'id'            => ['type' => self::T_INT],
            'title'         => ['type' => self::T_KEYWORD],
            'user_id'       => ['type' => self::T_INT],
            'visibility'    => ['type' => self::T_SHORT],
            'created'       => ['type' => self::T_DATE],
            'updated'       => ['type' => self::T_DATE],
        ],
    ];

    const MAIL_MAPPING = [
        //'_source'    => ['enabled' => true],
        '_parent'    => ['type' => self::O_PORTAL],
        '_routing'   => ['required' => true],
        'properties' => [
            'id'            => ['type' => self::T_INT],
            'recipient'     => ['type' => self::T_KEYWORD],
            'sender'        => ['type' => self::T_KEYWORD],
            'cc'            => ['type' => self::T_KEYWORD],
            'bcc'           => ['type' => self::T_KEYWORD],
            'subject'       => ['type' => self::T_KEYWORD],
            'body'          => ['type' => self::T_TEXT],
            'html'          => ['type' => self::T_TEXT],
            'context'       => ['type' => self::T_OBJECT],
            'options'       => ['type' => self::T_OBJECT],
            'attachments'   => ['type' => self::T_OBJECT],
            'timestamp'     => ['type' => self::T_DATE],
        ],
    ];
}
