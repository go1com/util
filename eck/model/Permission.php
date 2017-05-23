<?php

namespace go1\util\eck\model;

use go1\util\AccessChecker;
use InvalidArgumentException;

class Permission
{
    const REJECT = 0;
    const ACCEPT = 1;

    const ROLES      = [0, 1, 2, 3, 4];
    const ROLE_ALL   = 0;
    const ROLE_ROOT  = 1;
    const ROLE_ADMIN = 2;
    const ROLE_OWNER = 3;
    const ROLE_AUTH  = 4;

    const PERM_ALL    = [1, 2, 3, 4];
    const PERM_CREATE = 1;
    const PERM_READ   = 2;
    const PERM_UPDATE = 3;
    const PERM_DELETE = 4;

    public static function role(int $role): string
    {
        switch ($role) {
            case static::ROLE_ALL:
                return 'all';

            case static::ROLE_ROOT:
                return 'root';

            case static::ROLE_ADMIN:
                return 'admin';

            case static::ROLE_OWNER:
                return 'owner';

            case static::ROLE_AUTH:
                return 'auth';

            default:
                throw new InvalidArgumentException('Unknown role: ' . $role);
        }
    }

    public static function roleCode(string $role): int
    {
        switch ($role) {
            case 'all':
                return static::ROLE_ALL;

            case 'admin':
                return static::ROLE_ADMIN;

            case 'owner':
                return static::ROLE_OWNER;

            case 'auth':
                return static::ROLE_AUTH;

            default:
                throw new InvalidArgumentException('Unknown role: ' . $role);
        }
    }

    public static function permission(int $permission): string
    {
        switch ($permission) {
            case static::PERM_CREATE:
                return 'create';

            case static::PERM_READ:
                return 'read';

            case static::PERM_UPDATE:
                return 'update';

            case static::PERM_DELETE:
                return 'delete';

            default:
                throw new InvalidArgumentException('Unknown permission: ' . $permission);
        }
    }

    public static function permissionCode(string $permission): int
    {
        switch ($permission) {
            case 'create':
                return static::PERM_CREATE;

            case 'read':
                return static::PERM_READ;

            case 'update':
                return static::PERM_UPDATE;

            case 'delete':
                return static::PERM_DELETE;

            default:
                throw new InvalidArgumentException('Unknown permission: ' . $permission);
        }
    }

    public static function accessLevelToRoleCode(int $level): int
    {
        switch ($level) {
            case AccessChecker::ACCESS_ROOT:
                return static::ROLE_ROOT;

            case AccessChecker::ACCESS_ADMIN:
                return static::ROLE_ADMIN;

            case AccessChecker::ACCESS_AUTHENTICATED:
                return static::ROLE_AUTH;

            case AccessChecker::ACCESS_OWNER:
                return static::ROLE_OWNER;

            default:
                throw new InvalidArgumentException('Unknown access level: ' . $level);
        }
    }
}
