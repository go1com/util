<?php

namespace go1\clients;

use Doctrine\Common\Cache\CacheProvider;
use go1\util\user\UserHelper;
use GuzzleHttp\Client;

class EckClient
{
    private $client;
    private $url;
    private $cache;
    private $jwt = UserHelper::ROOT_JWT;

    public function __construct(Client $client, string $url, CacheProvider $cache)
    {
        $this->client = $client;
        $this->url = $url;
        $this->cache = $cache;
    }

    public function fields(string $instance, string $entityType, bool $isCache = false) : array
    {
        $cacheId = "fields:{$instance}:{$entityType}";

        if ($isCache && $this->cache->contains($cacheId)) {
            if ($fields = $this->cache->fetch($cacheId)) {
                return $fields;
            }
        }

        $data = $this->client->get("{$this->url}/fields/{$instance}/{$entityType}?jwt={$this->jwt}")->getBody()->getContents();
        $data = json_decode($data, true);

        $fields = [];
        if (!empty($data)) {
            foreach ($data['fields'] as $fieldName => $item) {
                $fields[$fieldName] = ['label' => $item['label'], 'type' => $item['type']];
            }
        }

        $ttl = 60 * 60; # 1 hour.
        $isCache && $this->cache->save($cacheId, $fields, $ttl);

        return $fields;
    }

    public function create(string $instance, string $entityType, int $entityId, array $fields)
    {
        $eckUrl = "{$this->url}/entity/{$instance}/{$entityType}/{$entityId}?jwt={$this->jwt}";
        $this->client->post($eckUrl, ['json' => ['fields' => $fields]]);
    }

    public function update(string $instance, string $entityType, int $entityId, array $fields)
    {
        $eckUrl = "{$this->url}/entity/{$instance}/{$entityType}/{$entityId}?jwt={$this->jwt}";
        $this->client->put($eckUrl, ['json' => ['fields' => $fields]]);
    }
}
