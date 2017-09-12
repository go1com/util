<?php

namespace go1\util\user;

class Roles
{
    const ROOT          = 'Admin on #Accounts';
    const ADMIN         = 'administrator';
    const AUTHENTICATED = 'authenticated user';
    const STUDENT       = 'Student';
    const TUTOR         = 'tutor';
    const ASSESSOR      = 'tutor';
    const MANAGER       = 'manager';
    const ANONYMOUS     = 'anonymous';

    const ACCOUNTS_ROLES = [self::ROOT, self::AUTHENTICATED];
    const PORTAL_ROLES   = [self::ANONYMOUS, self::AUTHENTICATED, self::ADMIN, self::STUDENT, self::TUTOR, self::MANAGER];

    const NAMES         = [
        self::ADMIN    => 'Administrator',
        self::STUDENT  => 'Learner',
        self::ASSESSOR => 'Assessor',
        self::MANAGER  => 'Manager',
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
