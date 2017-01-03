<?php

namespace go1\util;

class Roles
{
    const ROOT                  = 'Admin on #Accounts';
    const ADMIN                 = 'administrator';
    const AUTHENTICATED         = 'authenticated user';
    const STUDENT               = 'Student';
    const TUTOR                 = 'tutor';
    const ASSESSOR              = 'tutor';
    const MANAGER               = 'manager';
    const ANONYMOUS             = 'anonymous user';

    const ANONYMOUS_RID         = 1;
    const AUTHENTICATED_RID     = 2;
    const ADMIN_RID             = 30037204;
    const STUDENT_RID           = 66784200;
    const TUTOR_RID             = 126456107;
    const MANAGER_RID           = 52310416;

    const PORTAL_ROLES = [
        ['rid' => self::ANONYMOUS_RID, 'name' => self::ANONYMOUS],
        ['rid' => self::AUTHENTICATED_RID, 'name' => self::AUTHENTICATED],
        ['rid' => self::ADMIN_RID, 'name' => self::ADMIN],
        ['rid' => self::STUDENT_RID, 'name' => self::STUDENT],
        ['rid' => self::TUTOR_RID, 'name' => self::TUTOR],
        ['rid' => self::MANAGER_RID, 'name' => self::MANAGER]
    ];
}
