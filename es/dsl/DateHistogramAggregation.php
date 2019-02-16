<?php

namespace go1\util\es\dsl;

use ONGR\ElasticsearchDSL\Aggregation\Bucketing\DateHistogramAggregation as ONGRDateHistogramAggregation;
use DateTimeZone;

class DateHistogramAggregation extends ONGRDateHistogramAggregation
{
    private $minDocCount;
    private $timezone;

    public function __construct($name, $field = null, $interval = null, $format = null, int $minDocCount = null, DateTimeZone $timezone = null)
    {
        parent::__construct($name, $field, $interval, $format);
        $this->minDocCount = $minDocCount;
        $this->timezone = $timezone;
    }

    public function getArray()
    {
        $out = parent::getArray();
        $this->minDocCount && $out['min_doc_count'] = $this->minDocCount;
        $this->timezone && $out['time_zone'] = $this->timezone->getName();

        return $out;
    }
}
