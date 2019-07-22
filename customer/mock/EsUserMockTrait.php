<?php

namespace go1\util\customer\mock;

use Elasticsearch\Client;
use go1\util\customer\CustomerEsSchema;
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
            'allow_public' => $options['allow_public'] ?? 0,
            'roles'        => $options['roles'] ?? null,
            'avatar'       => $options['avatar'] ?? null,
            'fields'       => $options['fields'] ?? null,
            'timestamp'    => DateTime::formatDate($options['timestamp'] ?? time()),
            'metadata'     => [
                'instance_id' => $options['instance_id'] ?? 0,
                'updated_at'  => $options['updated_at'] ?? time(),
            ]
        ];

        $params = [
            'index'   => $options['index'] ?? CustomerEsSchema::INDEX,
            'type'    => CustomerEsSchema::O_USER,
            'id'      => $user['id'],
            'body'    => $user,
            'refresh' => true,
        ];

        if (isset($options['instance_id'])) {
            $params['routing'] = $options['instance_id'];
        }
        $client->create($params);

        return $user['id'];
    }

    public function createEsAccount(Client $client, $options = [])
    {
        static $autoId;

        $account = [
            'id'           => $options['id'] ?? ++$autoId,
            'instance'     => $options['instance'] ?? 'qa.mygo1.com',
            'mail'         => $options['mail'] ?? 'foo@mygo1.com',
            'name'         => $options['name'] ?? 'foo bar',
            'first_name'   => $options['first_name'] ?? '',
            'last_name'    => $options['last_name'] ?? '',
            'created'      => DateTime::formatDate($options['created'] ?? time()),
            'login'        => DateTime::formatDate($options['login'] ?? time()),
            'access'       => DateTime::formatDate($options['access'] ?? time()),
            'status'       => $options['status'] ?? 1,
            'allow_public' => $options['allow_public'] ?? 0,
            'profile_id'   => $options['profile_id'] ?? 0,
            'roles'        => $options['roles'] ?? null,
            'avatar'       => $options['avatar'] ?? null,
            'fields'       => $options['fields'] ?? null,
            'groups'       => $options['groups'] ?? null,
            'managers'     => $options['managers'] ?? null,
            'timestamp'    => DateTime::formatDate($options['timestamp'] ?? time()),
            'learning'     => $options['learning'] ?? null,
            'metadata'     => [
                'instance_id' => $options['instance_id'] ?? 0,
                'updated_at'  => $options['updated_at'] ?? time(),
            ],
        ];

        $client->create([
            'index'   => $options['index'] ?? CustomerEsSchema::INDEX,
            'routing' => $options['routing'] ?? $options['instance_id'],
            'type'    => CustomerEsSchema::O_ACCOUNT,
            'id'      => $account['id'],
            'body'    => $account,
            'refresh' => true,
        ]);

        return $account['id'];
    }
}
