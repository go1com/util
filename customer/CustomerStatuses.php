<?php

namespace go1\util\customer;

use ReflectionClass;

class CustomerStatuses
{
    const PROPOSAL   = 'Proposal';
    const ONBOARDING = 'Onboarding';
    const LIVE       = 'Live';
    const CANCELLED  = 'Cancelled';
    const SUSPENDED  = 'Suspended';

    public static function all()
    {
        $rClass = new ReflectionClass(self::class);

        return $rClass->getConstants();
    }
}
