<?php

namespace go1\util\group;

class GroupTypes
{
    const DEFAULT       = 'default'; // Discussion group, created by users or portal admin.
    const MARKETPLACE   = 'marketplace'; // Marketplace group, created by staff, contain premium groups & portal
    const PREMIUM       = 'premium'; // Premium group, created by staff, contain courses
    const SYSTEM        = 'system'; // Portal group, created by onboard wizard or portal sharing process, contain any items shared to a portal

    const ALL = [self::DEFAULT, self::MARKETPLACE, self::PREMIUM, self::SYSTEM];
}
