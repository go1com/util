<?php
namespace go1\util\es\dsl;

use ONGR\ElasticsearchDSL\Aggregation\Metric\TopHitsAggregation as ONGRTopHitsAggregation;

class TopHitsAggregation extends ONGRTopHitsAggregation
{
    private $params;

    public function __construct($name, $size = null, $from = null, $sort = null, $params = [])
    {
        parent::__construct($name, $size, $from, $sort);
        $this->params = $params;
    }

    public function getArray()
    {
        $output = array_filter(
            [
                'sort'    => $this->getSort() ? $this->getSort()->toArray() : null,
                'size'    => $this->getSize(),
                'from'    => $this->getFrom(),
            ] + $this->params,
            function ($val) {
                return (($val || is_array($val) || ($val || is_numeric($val))));
            }
        );

        return empty($output) ? new \stdClass() : $output;
    }
}
