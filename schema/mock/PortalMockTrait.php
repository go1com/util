<?php

namespace go1\util\schema\mock;

use Doctrine\DBAL\Connection;
use go1\util\edge\EdgeTypes;
use go1\util\portal\PortalHelper;

trait PortalMockTrait
{
    /**
     * @deprecated
     */
    public function createInstance(Connection $db, array $options)
    {
        return $this->createPortal($db, $options);
    }

    public function createPortal(Connection $db, array $options)
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

    /**
     * @deprecated
     */
    public function createInstancePrivateKey(Connection $db, array $options)
    {
        return $this->createPortalPrivateKey($db, $options);
    }

    public function createPortalPrivateKey(Connection $db, array $options)
    {
        return $this->createPortalPublicKey($db, $options + ['uuid' => uniqid("PRIVATE_KEY_")], 'user.1');
    }

    /**
     * @deprecated
     */
    public function createInstancePublicKey(Connection $db, array $options, $magic = 'user.0')
    {
        return $this->createPortalPublicKey($db, $options, $magic);
    }

    public function createPortalPublicKey(Connection $db, array $options, $magic = 'user.0')
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

    /**
     * @deprecated
     */
    public function createInstanceConfig(Connection $db, array $options)
    {
        return $this->createPortalConfig($db, $options);
    }

    public function createPortalConfig(Connection $db, array $options)
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

    public function createPortalData(Connection $db, array $options)
    {
        $db->insert('portal_data', [
            'id'              => $options['id'] ?? null,
            'state'           => $options['state'] ?? null,
            'type'            => $options['type'] ?? null,
            'channel'         => $options['channel'] ?? null,
            'plan'            => $options['plan'] ?? null,
            'customer_id'     => $options['customer_id'] ?? null,
            'partner_id'      => $options['partner_id'] ?? null,
            'conversion_date' => $options['conversion_date'] ?? null,
            'go_live_date'    => $options['go_live_date'] ?? null,
            'expiry_date'     => $options['expiry_date'] ?? null,
            'industry'        => $options['industry'] ?? null,
        ]);

        return $db->lastInsertId('portal_data');
    }

    public function createPortalDomain(Connection $go1, int $portalId, $domain): int
    {
        $go1->insert('gc_domain', ['title' => $domain]);
        $go1->insert('gc_ro', [
            'type'      => EdgeTypes::HAS_DOMAIN,
            'source_id' => $portalId,
            'target_id' => $domainId = $go1->lastInsertId('gc_domain'),
            'weight'    => 0,
        ]);

        return $domainId;
    }
}
