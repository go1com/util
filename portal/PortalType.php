<?php

namespace go1\util\portal;

use InvalidArgumentException;

class PortalType
{
    const CONTENT_PARTNER      = 'content_partner';
    const DISTRIBUTION_PARTNER = 'distribution_partner';
    const INTERNAL             = 'internal';
    const CUSTOMER             = 'customer';
    const COMPLISPACE          = 'complispace';
    const JSE_CUSTOMER         = 'jse_customer';
    const TOTARA_CUSTOMER      = 'totara_customer';
    const PORTAL_LAUNCHER      = 'portal_launcher';

    public static function all()
    {
        static $types;

        if (!isset($types)) {
            $reflectedClass = new \ReflectionClass(__CLASS__);
            $constants = $reflectedClass->getConstants();
            $types = array_values($constants);
        }

        return $types;
    }

    public static function toString(string $type): string
    {
        if (!in_array($type, self::all(), true)) {
            throw new InvalidArgumentException('Unknown portal type: ' . $type);
        }

        //Special formatting
        switch ($type) {
            case self::JSE_CUSTOMER:
                return 'JSE Customer';
        }

        //Default formatting
        return implode(' ', array_map('ucwords', explode('_', $type)));
    }
}
