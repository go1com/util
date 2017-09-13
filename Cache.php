<?php

namespace go1\util;

use Doctrine\Common\Cache\Cache as CacheInterface;

class Cache
{
    public static function cacheId(string $entityType, string $entityId)
    {
        return "{$entityType}:{$entityId}";
    }

    public static function eTag(string $entityType, string $entityId, int $update): string
    {
        return md5(self::cacheId($entityType, $entityId) . ":etag:{$update}");
    }

    public static function setETag(CacheInterface $cache, string $cacheId, string $eTag)
    {
        # Invalid previous eTag.
        if ($cache->contains("{$cacheId}:eTag")) {
            if ($prevETag = $cache->fetch("{$cacheId}:eTag")) {
                $cache->delete($prevETag);
            }
        }

        $cache->save("{$cacheId}:eTag", $eTag);
        $cache->save($eTag, time());
    }

    public static function invalidate(CacheInterface $cache, string $cacheId)
    {
        if ($cache->contains("{$cacheId}:eTag")) {
            if ($prevETag = $cache->fetch("{$cacheId}:eTag")) {
                $cache->delete($prevETag);
            }

            $cache->delete("$cacheId:eTag");
        }
    }
}
