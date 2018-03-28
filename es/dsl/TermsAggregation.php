<?php

namespace go1\util\es\dsl;

use ONGR\ElasticsearchDSL\Aggregation\Bucketing\TermsAggregation as ONGRTermsAggregation;

class TermsAggregation extends ONGRTermsAggregation
{
    private $size;
    private $params;

    public function __construct($name, $field = null, $script = null, $size = 10, array $params = [])
    {
        parent::__construct($name, $field, $script);
        $this->size = $size;
        $this->params = $params;
    }

    public function getArray()
    {
        return array_filter(
            [
                'field'   => $this->getField(),
                'script'  => $this->getScript(),
                'size'    => $this->size,
                'include' => $this->params['include'] ?? null,
                'exclude' => $this->params['exclude'] ?? null,
            ]
        );
    }
}
