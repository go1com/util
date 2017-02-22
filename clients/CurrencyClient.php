<?php

namespace go1\clients;

use Doctrine\Common\Cache\CacheProvider;
use GuzzleHttp\Client;
use RuntimeException;

class CurrencyClient
{
    private $cache;
    private $client;
    private $currencyUrl;

    public function __construct(CacheProvider $cache, Client $client, string $currencyUrl)
    {
        $this->cache = $cache;
        $this->client = $client;
        $this->currencyUrl = rtrim($currencyUrl, '/');
    }

    private function rate(string $from, string $to): float
    {
        if ($from == $to) {
            return 1.0;
        }

        $cacheId = "currency:rate:{$from}:{$to}";
        $cacheTtl = 60 * 60; # 1 hour.
        $url = "{$this->currencyUrl}/{$from}/{$to}";

        if ($this->cache->contains($cacheId)) {
            if ($rate = $this->cache->fetch($cacheId)) {
                return $rate;
            }
        }

        $res = $this->client->get($url, ['Content-Type' => 'application/json']);
        if (!$rate = json_decode($res->getBody()->getContents())->value) {
            throw new RuntimeException('Failed to get currency rate.');
        }

        $this->cache->save($cacheId, $rate, $cacheTtl);

        return $rate;
    }

    public function convert(float $amount, string $from, string $to): float
    {
        $value = $amount * $this->rate($from, $to);

        return round($value);
    }
}
