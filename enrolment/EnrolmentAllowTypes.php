<?php

namespace go1\util\enrolment;

use ReflectionClass;

class EnrolmentAllowTypes
{
    const DEFAULT = 'allow';
    const ENQUIRY = 'enquiry';
    const DISABLE = 'disable';

    // Numeric values for the types. Being used in ES.
    const I_DISABLE = 0;
    const I_ENQUIRY = 10;
    const I_DEFAULT = 20;

    public static function all()
    {
        $rClass = new ReflectionClass(self::class);

        return $rClass->getConstants();
    }
}
