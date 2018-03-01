<?php

namespace go1\util\es;

use Elasticsearch\Client;
use RuntimeException;

class DocumentRepository
{
    private $client;
    private $options;

    public function __construct(Client $client, array $requestOptions = [])
    {
        $this->client = $client;
        $this->options = $requestOptions;
    }

    private function indexName(string $documentType)
    {
        foreach (Schema::I_INDICES as $indexName => $types) {
            if (isset($types[$documentType])) {
                return $indexName;
            }
        }

        throw new RuntimeException('Unknown document type.');
    }

    private function filterNull($params)
    {
        return array_filter($params, function($value) {
           return !is_null($value);
        });
    }

    public function get(string $type, string $id, int $portalId = null, array $options = [])
    {
        return $this->client->get($this->filterNull([
            'index'   => $this->indexName($type),
            'type'    => $type,
            'id'      => $id,
            'routing' => $portalId,
        ] + $this->options + $options));
    }

    public function delete(Document $document, array $options = [])
    {
        return $this->client->delete($this->filterNull([
            'index'   => $this->indexName($document->type),
            'type'    => $document->type,
            'id'      => $document->id,
            'routing' => $document->portalId,
        ] + $this->options + $options));
    }

    public function save(Document $document, array $options = [])
    {
        return $this->client->index($this->filterNull([
            'index'   => $this->indexName($document->type),
            'type'    => $document->type,
            'id'      => $document->id,
            'routing' => $document->portalId,
            'parent'  => $document->parentId,
            'body'    => $document->body
        ] + $this->options + $options));
    }
}
