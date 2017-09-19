<?php

namespace go1\util\group;

class GroupTypes
{
    const DEFAULT           = 'default'; // discussion groups per portal, contain NOTEs & USERs
    const CONTENT           = 'content'; // contain COURSEs per portal
    const CONTENT_PACKAGE   = 'content_package'; // contain CONTENT groups & PORTALs
    const SYSTEM            = 'system'; // Portal group, created by onboard wizard or portal sharing process, contain any items shared to a portal

    const ALL = [self::DEFAULT, self::CONTENT_PACKAGE, self::CONTENT, self::SYSTEM];
}
