<?php

namespace go1\util\user;

use Doctrine\Common\Cache\CacheProvider;

class UserHash
{
    /**
     * @param int      $userId
     * @param string[] $roles
     * @return string
     */
    public function hash($userId, array $roles)
    {
        $chunks = ['user', $userId];

        sort($roles);
        foreach ($roles as &$role) {
            $chunks[] = $role;
        }

        return md5(implode(':', $chunks));
    }

    /**
     * @param CacheProvider $cache
     * @param int           $userId
     * @param string[]      $roles
     * @return bool
     */
    public function flush(CacheProvider $cache, $userId, $roles)
    {
        return $cache->delete($this->hash($userId, $roles));
    }

    /**
     * @param CacheProvider $cache
     * @param int           $userId
     * @param string[]      $roles
     * @return bool
     */
    public function warm(CacheProvider $cache, $userId, array $roles)
    {
        return $cache->save($this->hash($userId, $roles), true);
    }

    /**
     * @param CacheProvider $cache
     * @param int           $userId
     * @param string[]      $roles
     * @return bool
     */
    public function found(CacheProvider $cache, $userId, array $roles)
    {
        return $cache->contains($this->hash($userId, $roles));
    }
}
