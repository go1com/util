<?php

namespace go1\util\schema\mock;

use Doctrine\DBAL\Connection;
use Firebase\JWT\JWT;
use go1\util\Error;
use go1\util\model\User;
use go1\util\user\Roles;
use go1\util\user\UserHelper;
use InvalidArgumentException;

define('DEFAULT_ACCOUNT_PROFILE_ID', 11);
define('DEFAULT_ACCOUNT_ID', 1);
define('DEFAULT_USER_PROFILE_ID', 911);
define('DEFAULT_USER_ID', 91);

trait UserMockTrait
{
    public function createAccountsAdminRole($db, array $options = [])
    {
        return $this->createRole($db, $options + ['name' => Roles::ROOT]);
    }

    public function createPortalAdminRole($db, array $options = [])
    {
        return $this->createRole($db, $options + ['name' => Roles::ADMIN]);
    }

    public function createPortalContentAdminRole($db, array $options = [])
    {
        return $this->createRole($db, $options + ['name' => Roles::ADMIN_CONTENT]);
    }

    public function createPortalManagerRole($db, array $options = [])
    {
        return $this->createRole($db, $options + ['name' => Roles::MANAGER]);
    }

    public function createRole(Connection $db, array $options)
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

    public function createUser(Connection $db, array $options = []): int
    {
        static $profileId = 15;

        $data = isset($options['data']) ? $options['data'] : '[]';
        $data = is_scalar($data) ? $data : json_encode($data);

        $db->insert('gc_user', [
            'id'           => $options['id'] ?? null,
            'uuid'         => isset($options['uuid']) ? $options['uuid'] : uniqid('xxxxxxxx'),
            'instance'     => isset($options['instance']) ? $options['instance'] : 'az.mygo1.com',
            'profile_id'   => isset($options['profile_id']) ? $options['profile_id'] : $profileId++,
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

    # NOTE: This is not yet stable, JWT is large, not good for production usage.
    public function jwtForUser(Connection $db, int $userId, string $portalName = null): string
    {
        $payload = [
            'iss'    => 'go1.user',
            'ver'    => '1.1',
            'exp'    => strtotime('+ 1 year'),
            'object' => (object) [
                'type'    => 'user',
                'content' => call_user_func(
                    function () use ($db, $userId, $portalName) {
                        $user = UserHelper::load($db, $userId);
                        $user = $user ? User::create($user, $db, true, $portalName) : null;

                        if ($user && !empty($user->accounts[0])) {
                            $account = &$user->accounts[0];
                            $account->portalId = (int) $db->fetchColumn('SELECT id FROM gc_instance WHERE title = ?', [$account->instance]);
                        }

                        return $user;
                    }
                ),
            ],
        ];

        !$payload['object']->content && Error::throw(new InvalidArgumentException('User not found.'));

        return JWT::encode($payload, 'INTERNAL');
    }

    /**
     * @deprecated
     * @param string $mail
     * @param string $accountName
     * @param string $portalName
     * @param array  $roles
     * @param int    $accountProfileId
     * @param int    $accountId
     * @param int    $userProfileId
     * @param int    $userId
     * @param bool   $encode
     * @return object|string
     */
    protected function getJwt(
        $mail = 'thehongtt@gmail.com',
        $accountName = 'accounts.gocatalyze.com',
        $portalName = 'az.mygo1.com',
        $roles = ['authenticated'],
        $accountProfileId = DEFAULT_ACCOUNT_PROFILE_ID,
        $accountId = DEFAULT_ACCOUNT_ID,
        $userProfileId = DEFAULT_USER_PROFILE_ID,
        $userId = DEFAULT_USER_ID,
        $encode = true
    )
    {
        $payload = $this->getPayload([
            'id'              => $accountId,
            'accounts_name'   => $accountName,
            'instance_name'   => $portalName,
            'profile_id'      => $accountProfileId,
            'mail'            => $mail,
            'roles'           => $roles,
            'user_id'         => $userId,
            'user_profile_id' => $userProfileId,
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
        $accountId = isset($options['id']) ? $options['id'] : DEFAULT_ACCOUNT_ID;
        $accountProfileId = isset($options['profile_id']) ? $options['profile_id'] : DEFAULT_ACCOUNT_PROFILE_ID;
        $userId = isset($options['user_id']) ? $options['user_id'] : $accountId;
        $userProfileId = isset($options['user_profile_id']) ? $options['user_profile_id'] : $accountProfileId;
        $mail = isset($options['mail']) ? $options['mail'] : 'thehongtt@gmail.com';
        $roles = isset($options['roles']) ? $options['roles'] : ['authenticated'];

        $account = [
            'id'            => intval($accountId),
            'first_name'    => 'A',
            'last_name'     => 'T',
            'status'        => 1,
            'roles'         => $roles,
            'instance_name' => isset($options['instance_name']) ? $options['instance_name'] : 'az.mygo1.com',
            'mail'          => $mail,
            'profile_id'    => intval($accountProfileId),
        ];

        if (isset($options['portal_id'])) {
            $account['portal_id'] = $options['portal_id'];
        }

        $user = [
            'id'            => intval($userId),
            'first_name'    => 'A',
            'last_name'     => 'T',
            'instance_name' => isset($options['accounts_name']) ? $options['accounts_name'] : 'accounts.gocatalyze.com',
            'profile_id'    => intval($userProfileId),
            'mail'          => $mail = isset($options['mail']) ? $options['mail'] : 'thehongtt@gmail.com',
            'roles'         => $roles = isset($options['roles']) ? $options['roles'] : ['authenticated'],
            'accounts'      => [
                (object) $account
            ],
        ];

        return (object) [
            'iss'    => 'go1.user',
            'ver'    => '1.1',
            'exp'    => strtotime('+ 1 year'),
            'object' => (object) [
                'type'    => 'user',
                'content' => $this->formatUser($user, true, "{$user['first_name']} {$user['last_name']}"),
            ],
        ];
    }

    protected function getPayloadMultipleInstances(array $options)
    {
        $accountId = isset($options['id']) ? $options['id'] : DEFAULT_ACCOUNT_ID;
        $accountProfileId = isset($options['profile_id']) ? $options['profile_id'] : DEFAULT_ACCOUNT_PROFILE_ID;
        $userId = isset($options['user_id']) ? $options['user_id'] : $accountId;
        $userProfileId = isset($options['user_profile_id']) ? $options['user_profile_id'] : $accountProfileId;
        $accountsName = isset($options['accounts_name']) ? $options['accounts_name'] : 'accounts.gocatalyze.com';

        $user = [
            'id'            => intval($userId),
            'first_name'    => 'A',
            'last_name'     => 'T',
            'instance_name' => $accountsName,
            'profile_id'    => intval($userProfileId),
            'mail'          => $mail = isset($options['mail']) ? $options['mail'] : 'thehongtt@gmail.com',
            'roles'         => isset($options['roles'][$accountsName]) ? $options['roles'][$accountsName] : ['authenticated'],
        ];

        foreach ($options['instance_name'] as $instanceName) {
            $user['accounts'][] = (object) [
                'id'            => isset($options['ids'][$instanceName]) ? $options['ids'][$instanceName] : intval($accountId),
                'first_name'    => 'A',
                'last_name'     => 'T',
                'status'        => 1,
                'roles'         => isset($options['roles'][$instanceName]) ? $options['roles'][$instanceName] : ['student'],
                'instance_name' => $instanceName,
                'mail'          => $mail,
                'profile_id'    => isset($options['profile_ids'][$instanceName]) ? $options['profile_ids'][$instanceName] : intval($accountProfileId),
            ];
        }

        return (object) [
            'iss'    => 'go1.user',
            'ver'    => '1.1',
            'exp'    => strtotime('+ 1 year'),
            'object' => (object) [
                'type'    => 'user',
                'content' => $this->formatUser($user, true, "{$user['first_name']} {$user['last_name']}"),
            ],
        ];
    }

    private function formatUser(array $user, $root = true, $username = null)
    {
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
                'portal_id'  => !empty($user['portal_id']) ? $user['portal_id'] : null,
                'mail'       => $root ? $user['mail'] : null,
                'name'       => $root ? $name : (($username === $name) ? null : $name),
                'roles'      => $roles ?? [],
                'accounts'   => $accounts ?? [],
            ]
        );
    }

    public function link(Connection $db, $type, $sourceId, $targetId, $weight = 0, $data = null): int
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
