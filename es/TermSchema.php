<?php

namespace go1\util\es;

/**
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/mapping-types.html
 */
class TermSchema
{
    const INDEX = Schema::INDEX . '_term';

    const O_TERM = 'term';

    const SCHEMA = [
        'index' => self::INDEX,
        'body'  => self::BODY,
    ];

    const BODY = [
        'mappings' => self::MAPPING,
    ];

    const MAPPING = [
        self::O_TERM => self::TERM_MAPPING,
    ];

    const TERM_MAPPING = [
        '_routing'   => ['required' => true],
        'properties' => [
            'term'  => [
                'type'                         => Schema::T_COMPLETION,
                'analyzer'                     => Schema::A_SIMPLE,
                'preserve_separators'          => true,
                'preserve_position_increments' => true,
                'max_input_length'             => Schema::MAX_INPUT_LENGTH,
                'contexts'                     => [
                    [
                        'name' => 'topic',
                        'type' => Schema::T_COMPLETION_CATEGORY,
                        'path' => 'topic',
                    ],
                ],
            ],
            'topic' => [
                'type' => Schema::T_TEXT,
            ],
        ],
    ];
}
