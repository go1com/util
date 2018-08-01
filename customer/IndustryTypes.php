<?php

namespace go1\util\customer;

use ReflectionClass;

class IndustryTypes
{
    const AEROSPACE                     = 'Aerospace';
    const AGRICULTURE                   = 'Agriculture';
    const CHEMICAL                      = 'Chemical';
    const COMPUTER                      = 'Computer';
    const CONSTRUCTION_INFRASTRUCTURE   = 'Construction and Infrastructure';
    const REAL_ESTATE                   = 'Real Estate';
    const PUBLIC_UTILITIES              = 'Public Utilities';
    const DEFENSE                       = 'Defense';
    const ENTERTAINMENT                 = 'Entertainment';
    const ENERGY                        = 'Energy';
    const EDUCATION                     = 'Education';
    const BANKING_FINANCIAL_SERVICES    = 'Banking and Financial Services';
    const HEALTHCARE                    = 'Healthcare';
    const HOSPITALITY                   = 'Hospitality';
    const INFORMATION                   = 'Information';
    const MANUFACTURING                 = 'Manufacturing';
    const MEDIA                         = 'Media';
    const MINING                        = 'Mining';
    const TELECOMMUNICATIONS            = 'Telecommunications';
    const TRANSPORT                     = 'Transport';

    public static function getArray()
    {
        $rClass = new ReflectionClass(self::class);

        return $rClass->getConstants();
    }
}
