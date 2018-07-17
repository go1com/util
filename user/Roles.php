<?php

namespace go1\util\user;

class Roles
{
    const ROOT          = 'Admin on #Accounts';
    const ADMIN         = 'administrator';
    const ADMIN_CONTENT = 'content administrator';
    const DEVELOPER     = 'developer';
    const AUTHENTICATED = 'authenticated user';
    const STUDENT       = 'Student';
    const TUTOR         = 'tutor';
    const ASSESSOR      = 'tutor';
    const MANAGER       = 'manager';
    const TAM           = 'training account manager';
    const ANONYMOUS     = 'anonymous';

    const ACCOUNTS_ROLES     = [self::ROOT, self::DEVELOPER, self::AUTHENTICATED, self::TAM];
    const PORTAL_ROLES       = [self::ANONYMOUS, self::AUTHENTICATED, self::ADMIN, self::ADMIN_CONTENT, self::STUDENT, self::TUTOR, self::MANAGER];
    const PORTAL_ADMIN_ROLES = [self::ADMIN, self::ADMIN_CONTENT, self::MANAGER]; # Roles can access portal admin area.

    const NAMES = [
        self::ADMIN         => 'Administrator',
        self::STUDENT       => 'Learner',
        self::ASSESSOR      => 'Assessor',
        self::MANAGER       => 'Manager',
        self::ADMIN_CONTENT => 'Content administrator',
    ];

    public static function getRoleByName(string $roleName)
    {
        if ($roleName == self::STUDENT) {
            return self::STUDENT;
        }

        foreach (self::NAMES as $role => $name) {
            if ($name == $roleName) {
                return $role;
            }
        }

        return false;
    }
}
