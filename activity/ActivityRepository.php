<?php

namespace go1\util\activity;

use Elasticsearch\Client;
use go1\util\DateTime;
use go1\util\es\dsl\TermsAggregation;
use go1\util\es\Schema;
use ONGR\ElasticsearchDSL\Query\Compound\BoolQuery;
use ONGR\ElasticsearchDSL\Query\TermLevel\RangeQuery;
use ONGR\ElasticsearchDSL\Query\TermLevel\TermQuery;
use ONGR\ElasticsearchDSL\Search;
use ONGR\ElasticsearchDSL\BuilderInterface;
use ONGR\ElasticsearchDSL\Sort\FieldSort;

class ActivityRepository
{
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function getByUserId(int $portalId, int $accountId, int $offset, int $limit, string $sort = FieldSort::ASC, BuilderInterface $filter = null): array
    {
        $userQuery = new BoolQuery();
        $userQuery->add(new TermQuery('actor_id', $accountId), BoolQuery::SHOULD);
        $userQuery->add(new TermQuery('user_id', $accountId), BoolQuery::SHOULD);

        $query = new BoolQuery();
        $query->add(new TermQuery('instance_id', $portalId), BoolQuery::MUST);
        $query->add($userQuery, BoolQuery::MUST);
        $filter && $query->add($filter, BoolQuery::MUST);

        $search = new Search();
        $search
            ->setFrom($offset)
            ->setSize($limit)
            ->addSort(new FieldSort('created', $sort))
            ->addQuery($query);

        return $this->client->search([
            'index'              => Schema::ACTIVITY_INDEX,
            'type'               => Schema::O_ACTIVITY,
            'body'               => $search->toArray(),
            'ignore_unavailable' => true,
        ]);
    }

    public function getByPortal(int $portalId, int $offset, int $limit, string $sort = FieldSort::ASC, BuilderInterface $filter = null): array
    {
        $query = new BoolQuery();
        $query->add(new TermQuery('instance_id', $portalId), BoolQuery::MUST);
        $filter && $query->add($filter, BoolQuery::MUST);

        $search = new Search();
        $search
            ->setFrom($offset)
            ->setSize($limit)
            ->addSort(new FieldSort('created', $sort))
            ->addQuery($query);

        return $this->client->search([
            'index'              => Schema::ACTIVITY_INDEX,
            'type'               => Schema::O_ACTIVITY,
            'body'               => $search->toArray(),
            'ignore_unavailable' => true,
        ]);
    }

    public function getByLoId(int $portalId, int $loId, int $offset, int $limit, string $sort = FieldSort::ASC, BuilderInterface $filter = null): array
    {
        $query = new BoolQuery();
        $query->add(new TermQuery('tags', "lo:$loId"), BoolQuery::MUST);
        $query->add(new TermQuery('instance_id', $portalId), BoolQuery::MUST);
        $filter && $query->add($filter, BoolQuery::MUST);

        $search = new Search();
        $search
            ->setFrom($offset)
            ->setSize($limit)
            ->addSort(new FieldSort('created', $sort))
            ->addQuery($query);

        return $this->client->search([
            'index'              => Schema::ACTIVITY_INDEX,
            'type'               => Schema::O_ACTIVITY,
            'body'               => $search->toArray(),
            'ignore_unavailable' => true,
        ]);
    }

    public function getPortalActive($start, $end): array
    {
        $query = new BoolQuery();
        $query->add(new RangeQuery('created', [
            RangeQuery::GTE => $start,
            RangeQuery::LTE => $end]),
            BoolQuery::MUST);

        $termsAgg = new TermsAggregation('aggs', 'instance_id', null, 0);
        $search = new Search();
        $search->setSize(0)
            ->addAggregation($termsAgg)
            ->addQuery($query);
        $searchResult = $this->client->search([
            'index'              => Schema::ACTIVITY_INDEX,
            'type'               => Schema::O_ACTIVITY,
            'body'               => $search->toArray(),
            'ignore_unavailable' => true,
        ]);
        $result = [];
        foreach ($searchResult['aggregations']['aggs']['buckets'] as $item) {
            array_push($result, (object)[
                'id'    => $item['key'],
                'value' => $item['doc_count'],
            ]);
        }
        return $result;
    }

}
