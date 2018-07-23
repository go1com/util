<?php

namespace go1\util\user;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use go1\clients\MqClient;
use go1\util\edge\EdgeHelper;
use go1\util\edge\EdgeTypes;
use go1\util\queue\Queue;
use PDO;

/**
 * @deprecated This should be part of #user, other service use UserClient to manage role instead of this.
 */
class RoleHelper
{
    public static function grant(Connection $go1, MqClient $queue, string $portalName, int $userId, string $role)
    {
        $roleId = self::roleId($go1, $queue, $portalName, $role);
        if ($roleId) {
            try {
                EdgeHelper::link($go1, $queue, EdgeTypes::HAS_ROLE, $userId, $roleId);

                return $roleId;
            }
            catch (DBALException $e) {
                return false;
            }
        }

        return false;
    }

    public static function roleId(Connection $db, MqClient $mqClient, string $instance, string $role): int
    {
        $roleId = $db->fetchColumn('SELECT id FROM gc_role WHERE instance = ? AND name = ?', [$instance, $role]);

        // Instance can be onboarding, the roles is not created yet, we create fake role.
        // It will be corrected when the instance is active.
        if (!$roleId) {
            $roleId = self::add($db, $mqClient, $instance, $role);
        }

        return $roleId;
    }

    public static function add(Connection $db, MqClient $queue, string $instance, string $role)
    {
        $db->insert('gc_role', $message = [
            'instance'   => $instance,
            'rid'        => 0,
            'name'       => $role,
            'weight'     => 0,
            'permission' => json_encode(['access content', 'access entities']),
        ]);

        $queue->publish($message, Queue::ROLE_CREATE);

        return $db->lastInsertId('gc_role');
    }

    public static function roleIds(Connection $db, string $portalName, array $roles): array
    {
        return $db
            ->executeQuery(
                'SELECT id FROM gc_role WHERE instance = ? AND name IN (?)',
                [$portalName, $roles],
                [PDO::PARAM_STR, Connection::PARAM_STR_ARRAY]
            )
            ->fetchAll(PDO::FETCH_COLUMN);
    }
}
