<?php

namespace go1\util\plan;


use ReflectionClass;

class PlanStatuses
{
    const INTERESTING = -4; # Learner interest in the object, but no action provided yet.
    const SCHEDULED   = -3; # Learner is scheduled in the object.
    const ASSIGNED    = -2; # Learner self-assigned, or by someone.
    const ENQUIRED    = -1; # Learner interesting in the object, enquired.
    const PENDING     = 0; # The object is not yet available.
    const LATE        = 4; # Learning was assigned & was not able to complete the plan ontime.
    const EXPIRED     = 5; # The object is expired.

    public static function all()
    {
        $rClass = new ReflectionClass(self::class);

        return $rClass->getConstants();
    }
}
