<?php

namespace go1\util\es\mock;

use Elasticsearch\Client;
use go1\util\es\Schema;
use go1\util\DateTime;

trait EsUserMockTrait
{
    public function createEsUser(Client $client, $options = [])
    {
        static $autoId;

        $user = [
            'id'           => $options['id'] ?? ++$autoId,
            'profile_id'   => $options['profile_id'] ?? 0,
            'mail'         => $options['mail'] ?? 'foo@mygo1.com',
            'name'         => $options['name'] ?? 'foo bar',
            'first_name'   => $options['first_name'] ?? '',
            'last_name'    => $options['last_name'] ?? '',
            'created'      => DateTime::formatDate($options['created'] ?? time()),
            'login'        => DateTime::formatDate($options['login'] ?? time()),
            'access'       => DateTime::formatDate($options['access'] ?? time()),
            'status'       => $options['status'] ?? 1,
            'allow_public' => $options['allow_public'] ?? false,
            'roles'        => $options['roles'] ?? null,
            'avatar'       => $options['avatar'] ?? null,
        ];

        return $client->create([
            'index'   => Schema::INDEX,
            'routing' => Schema::INDEX,
            'type'    => Schema::O_USER,
            'id'      => $user['id'],
            'body'    => $user,
            'parent'  => null,
        ]);
    }
}
