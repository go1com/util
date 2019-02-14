<?php

namespace go1\util\es\dsl;

use ONGR\ElasticsearchDSL\Aggregation\Bucketing\DateHistogramAggregation as ONGRDateHistogramAggregation;

class DateHistogramAggregation extends ONGRDateHistogramAggregation
{
    private $minDocCount;

    public function __construct($name, $field = null, $interval = null, $format = null, int $minDocCount = null)
    {
        parent::__construct($name, $field, $interval, $format);
        $this->minDocCount = $minDocCount;
    }

    public function getArray()
    {
        $out = parent::getArray();
        $this->minDocCount && $out['min_doc_count'] = $this->minDocCount;

        return $out;
    }
}
