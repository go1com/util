<?php

namespace go1\util\schema\mock;

use Doctrine\DBAL\Connection;
use Firebase\JWT\JWT;

trait UserMockTrait
{
    public function createAccountsAdminRole($db, array $options = [])
    {
        return $this->createRole($db, $options + ['name' => 'Admin on #Accounts']);
    }

    public function createPortalAdminRole($db, array $options = [])
    {
        return $this->createRole($db, $options + ['name' => 'administrator']);
    }

    protected function createRole(Connection $db, array $options)
    {
        $db->insert('gc_role', [
            'instance'   => isset($options['instance']) ? $options['instance'] : 'az.mygo1.com',
            'rid'        => isset($options['rid']) ? $options['rid'] : rand(0, time()),
            'name'       => $options['name'],
            'weight'     => isset($options['weight']) ? $options['weight'] : 0,
            'permission' => isset($options['permissions']) ? implode(',', $options['permissions']) : '',
        ]);

        return $db->lastInsertId('gc_role');
    }

    protected function createUser(Connection $db, array $options = []): int
    {
        $data = isset($options['data']) ? $options['data'] : '[]';
        $data = is_scalar($data) ? $data : json_encode($data);

        $db->insert('gc_user', [
            'uuid'         => isset($options['uuid']) ? $options['uuid'] : uniqid('xxxxxxxx'),
            'instance'     => isset($options['instance']) ? $options['instance'] : 'az.mygo1.com',
            'profile_id'   => isset($options['profile_id']) ? $options['profile_id'] : 2,
            'mail'         => isset($options['mail']) ? $options['mail'] : 'thehongtt@gmail.com',
            'password'     => isset($options['password']) ? $options['password'] : 'xxxxxxx',
            'created'      => isset($options['created']) ? $options['created'] : strtotime('-10 days'),
            'login'        => isset($options['login']) ? $options['login'] : strtotime('-2 days'),
            'access'       => isset($options['access']) ? $options['access'] : strtotime('-1 days'),
            'status'       => isset($options['status']) ? $options['status'] : 1,
            'first_name'   => isset($options['first_name']) ? $options['first_name'] : 'A',
            'last_name'    => isset($options['last_name']) ? $options['last_name'] : 'T',
            'allow_public' => isset($options['allow_public']) ? $options['allow_public'] : 0,
            'data'         => $data,
            'timestamp'    => isset($options['timestamp']) ? $options['timestamp'] : time(),
        ]);

        return $db->lastInsertId('gc_user');
    }

    protected function getJwt(
        $mail = 'thehongtt@gmail.com',
        $accountName = 'accounts.gocatalyze.com',
        $instanceName = 'az.mygo1.com',
        $roles = ['authenticated'],
        $profileId = 11,
        $userId = 1,
        $encode = true
    )
    {
        $payload = $this->getPayload([
            'id'            => $userId,
            'accounts_name' => $accountName,
            'instance_name' => $instanceName,
            'profile_id'    => $profileId,
            'mail'          => $mail,
            'roles'         => $roles,
        ]);

        return $encode ? JWT::encode($payload, 'private_key') : $payload;
    }

    protected function getRootPayload()
    {
        return $this->getPayload(['roles' => ['Admin on #Accounts']]);
    }

    protected function getAdminPayload($instance, array $options = [])
    {
        return $this->getPayload(['roles' => ['administrator'], 'instance_name' => $instance] + $options);
    }

    protected function getPayload(array $options)
    {
        $userId = isset($options['id']) ? $options['id'] : 1;
        $profileId = isset($options['profile_id']) ? $options['profile_id'] : 11;

        $user = [
            'id'            => intval($userId),
            'first_name'    => 'A',
            'last_name'     => 'T',
            'instance_name' => isset($options['accounts_name']) ? $options['accounts_name'] : 'accounts.gocatalyze.com',
            'profile_id'    => intval($profileId),
            'mail'          => $mail = isset($options['mail']) ? $options['mail'] : 'thehongtt@gmail.com',
            'roles'         => $roles = isset($options['roles']) ? $options['roles'] : ['authenticated'],
            'accounts'      => [
                (object) [
                    'id'            => intval($userId),
                    'first_name'    => 'A',
                    'last_name'     => 'T',
                    'status'        => 1,
                    'roles'         => $roles,
                    'instance_name' => isset($options['instance_name']) ? $options['instance_name'] : 'az.mygo1.com',
                    'mail'          => $mail,
                    'profile_id'    => intval($profileId),
                ],
            ],
        ];

        $this->rootName = "{$user['first_name']} {$user['last_name']}";

        return (object) [
            'iss'    => 'go1.user',
            'ver'    => '1.1',
            'exp'    => strtotime('+ 1 year'),
            'object' => (object) [
                'type'    => 'user',
                'content' => $this->formatUser($user),
            ],
        ];
    }

    protected function getPayloadMultipleInstances(array $options)
    {
        $userId = isset($options['id']) ? $options['id'] : 1;
        $profileId = isset($options['profile_id']) ? $options['profile_id'] : 11;
        $accountsName = isset($options['accounts_name']) ? $options['accounts_name'] : 'accounts.gocatalyze.com';

        $user = [
            'id'            => intval($userId),
            'first_name'    => 'A',
            'last_name'     => 'T',
            'instance_name' => $accountsName,
            'profile_id'    => intval($profileId),
            'mail'          => $mail = isset($options['mail']) ? $options['mail'] : 'thehongtt@gmail.com',
            'roles'         => isset($options['roles'][$accountsName]) ? $options['roles'][$accountsName] : ['authenticated'],
        ];

        foreach ($options['instance_name'] as $instanceName) {
            $user['accounts'][] = (object) [
                'id'            => intval($userId),
                'first_name'    => 'A',
                'last_name'     => 'T',
                'status'        => 1,
                'roles'         => isset($options['roles'][$instanceName]) ? $options['roles'][$instanceName] : ['student'],
                'instance_name' => $instanceName,
                'mail'          => $mail,
                'profile_id'    => intval($profileId),
            ];
        }

        $this->rootName = "{$user['first_name']} {$user['last_name']}";

        return (object) [
            'iss'    => 'go1.user',
            'ver'    => '1.1',
            'exp'    => strtotime('+ 1 year'),
            'object' => (object) [
                'type'    => 'user',
                'content' => $this->formatUser($user),
            ],
        ];
    }

    private function formatUser($user, $root = true)
    {
        $accounts = [];

        if ($root && !empty($user['accounts'])) {
            foreach ($user['accounts'] as &$account) {
                if ($account->status) {
                    if (isset($account->instance)) {
                        unset($account->instance);
                    }

                    $accounts[] = $this->formatUser((array) $account, false);
                }
            }
        }

        $name = "{$user['first_name']} {$user['last_name']}";
        $roles = [];
        if (!empty($user['roles'])) {
            foreach ($user['roles'] as $role) {
                if ('authenticated user' !== $role) {
                    $roles[] = $role;
                }
            }
        }

        return (object) array_filter(
            [
                'id'         => intval($user['id']),
                'instance'   => !empty($user['instance_name']) ? $user['instance_name'] : null,
                'profile_id' => intval($user['profile_id']),
                'mail'       => $root ? $user['mail'] : null,
                'name'       => $root ? $name : (($this->rootName === $name) ? null : $name),
                'roles'      => $roles,
                'accounts'   => $accounts,
            ]
        );
    }

    protected function link(Connection $db, $type, $sourceId, $targetId, $weight = 0, $data = null): int
    {
        $db->insert('gc_ro', [
            'type'      => $type,
            'source_id' => $sourceId,
            'target_id' => $targetId,
            'weight'    => $weight,
            'data'      => is_scalar($data) ? $data : json_encode($data),
        ]);

        return $db->lastInsertId('gc_ro');
    }

    protected function addEmail(Connection $db, $email)
    {
        $db->insert('gc_user_mail', ['title' => $email]);

        return $db->lastInsertId('gc_user_mail');
    }
}
