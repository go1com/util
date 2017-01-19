<?php

namespace go1\util;

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
}
