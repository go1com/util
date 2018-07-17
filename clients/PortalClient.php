<?php

namespace go1\clients;

use Doctrine\Common\Cache\CacheProvider;
use go1\util\notify\MailTemplate;
use go1\util\user\UserHelper;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use InvalidArgumentException;

class PortalClient
{
    use RequestTrait;

    private $portalUrl;
    private $cache;
    private $cacheId = 'middleware:portal:%NAME%';

    public function __construct(Client $client, $portalUrl, CacheProvider $cache)
    {
        $this->setClient($client);
        $this->portalUrl = $portalUrl;
        $this->cache = $cache;
    }

    public function load(string $instance)
    {
        if (!$instance) {
            return null;
        }

        $this->cacheId = str_replace('%NAME%', $instance, $this->cacheId);
        $is404 = false;
        if ($this->cache->contains($this->cacheId)) {
            $portal = $this->cache->fetch($this->cacheId);
            $is404 = 404 === $portal;
        }

        if (empty($portal)) {
            $url = rtrim($this->portalUrl, '/') . "/{$instance}";
            $response = $this->request('GET', $url, [], []);
            $portal = json_decode($response->getBody()->getContents());
            if (!$portal || $response->getStatusCode() === 404) {
                $is404 = true;
                $this->cache->save($this->cacheId, 404, $ttl = 30);
            }
            else {
                $this->cache->save($this->cacheId, $portal, $tll = 120);
            }
        }

        return $is404 ? null : $portal;
    }

    public function configuration(string $instance, string $namespace, string $key, int $default = 1)
    {
        try {
            $res = $this->client->get("{$this->portalUrl}/conf/{$instance}/{$namespace}/{$key}?default={$default}&jwt=" . UserHelper::ROOT_JWT);
            if ($json = json_decode($res->getBody()->getContents())) {
                if (isset($json->data)) {
                    return $json->data;
                }
            }
        }
        catch (BadResponseException $e) {
        }

        return false;
    }

    public function mailTemplate($instance, $mailKey): MailTemplate
    {
        if (!$template = $this->configuration($instance, 'mail-template', $mailKey, 0)) {
            throw new InvalidArgumentException('Template not found.');
        }

        return new MailTemplate($mailKey, $template->subject, $template->body, isset($template->html) ? $template->html : null);
    }
}
