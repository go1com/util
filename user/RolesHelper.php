<?php

namespace go1\util\user;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use go1\clients\MqClient;
use go1\util\edge\EdgeHelper;
use go1\util\edge\EdgeTypes;

class RolesHelper
{
    public static function grant(Connection $db, MqClient $mqClient, string $instance, int $userId, string $role)
    {
        $roleId = self::roleId($db, $instance, $role);
        if ($roleId) {
            try {
                EdgeHelper::link($db, $mqClient, EdgeTypes::HAS_ROLE, $userId, $roleId);

                return $roleId;
            }
            catch (DBALException $e) {
                return false;
            }
        }

        return false;
    }

    public static function roleId(Connection $db, string $instance, string $role): int
    {
        $roleId = $db->fetchColumn('SELECT id FROM gc_role WHERE instance = ? AND name = ?', [$instance, $role]);

        // Instance can be onboarding, the roles is not created yet, we create fake role.
        // It will be corrected when the instance is active.
        if (!$roleId) {
            $roleId = self::add($db, $instance,$role);
        }

        return $roleId;
    }

    public static function add(Connection $db, string $instance, string $role)
    {
        $db->insert('gc_role', [
            'instance'   => $instance,
            'rid'        => 0,
            'name'       => $role,
            'weight'     => 0,
            'permission' => json_encode(['access content', 'access entities']),
        ]);

        return $db->lastInsertId('gc_role');
    }
}
