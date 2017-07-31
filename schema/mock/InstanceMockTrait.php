<?php

namespace go1\util\schema\mock;

use Doctrine\DBAL\Connection;
use go1\util\portal\PortalHelper;

trait InstanceMockTrait
{
    protected function createInstance(Connection $db, array $options)
    {
        $data = isset($options['data']) ? $options['data'] : '[]';

        $db->insert('gc_instance', [
            'id'         => $options['id'] ?? null,
            'title'      => $title = isset($options['title']) ? $options['title'] : 'az.mygo1.com',
            'status'     => isset($options['status']) ? $options['status'] : 1,
            'is_primary' => isset($options['is_primary']) ? $options['is_primary'] : 1,
            'version'    => isset($options['version']) ? $options['version'] : PortalHelper::STABLE_VERSION,
            'data'       => is_scalar($data) ? $data : json_encode($data),
            'timestamp'  => isset($options['timestamp']) ? $options['timestamp'] : time(),
            'created'    => isset($options['created']) ? $options['created'] : time(),
        ]);

        return $db->lastInsertId('gc_instance');
    }

    public function createInstancePrivateKey(Connection $db, array $options)
    {
        return $this->createInstancePublicKey($db, $options, 'user.1');
    }

    public function createInstancePublicKey(Connection $db, array $options, $magic = 'user.0')
    {
        static $profileId = 25;

        $db->insert('gc_user', [
            'instance'   => $instance = isset($options['instance']) ? $options['instance'] : 'az.mygo1.com',
            'uuid'       => $uuid = isset($options['uuid']) ? $options['uuid'] : uniqid("PUBLIC_KEY_{$instance}"),
            'profile_id' => isset($options['profile_id']) ? $options['profile_id'] : $profileId++,
            'mail'       => $magic . '@' . $instance,
            'password'   => isset($options['password']) ? $options['password'] : 'xxxxxxx',
            'created'    => isset($options['created']) ? $options['created'] : strtotime('-10 days'),
            'login'      => isset($options['login']) ? $options['login'] : strtotime('-2 days'),
            'access'     => isset($options['access']) ? $options['access'] : strtotime('-1 days'),
            'status'     => isset($options['status']) ? $options['status'] : 1,
            'first_name' => isset($options['first_name']) ? $options['first_name'] : 'A',
            'last_name'  => isset($options['last_name']) ? $options['last_name'] : 'T',
            'data'       => isset($options['data']) ? $options['data'] : '[]',
            'timestamp'  => isset($options['timestamp']) ? $options['timestamp'] : time(),
        ]);

        return $uuid;
    }

    public function createInstanceConfig(Connection $db, array $options)
    {
        $db->insert('portal_conf', [
            'instance'  => $instance = isset($options['instance']) ? $options['instance'] : 'az.mygo1.com',
            'namespace' => isset($options['namespace']) ? $options['namespace'] : 'foo',
            'name'      => isset($options['name']) ? $options['name'] : 'bar',
            'public'    => isset($options['public']) ? $options['public'] : 1,
            'data'      => isset($options['data']) ? $options['data'] : json_encode(['foo' => 'bar']),
            'timestamp' => isset($options['timestamp']) ? $options['timestamp'] : time(),
        ]);

        return true;
    }
}
