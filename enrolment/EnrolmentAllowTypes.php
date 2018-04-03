<?php

namespace go1\util\enrolment;

use InvalidArgumentException;

class EnrolmentAllowTypes
{
    const DEFAULT = 'allow';
    const ENQUIRY = 'enquiry';
    const DISABLE = 'disable';

    // Numeric values for the types. Being used in ES.
    const I_DISABLE = 0;
    const I_ENQUIRY = 10;
    const I_DEFAULT = 20;

    public static function toNumeric(string $type): int
    {
        switch ($type) {
            case self::DEFAULT:
                return self::I_DEFAULT;

            case self::ENQUIRY:
                return self::I_ENQUIRY;

            case self::DISABLE:
                return self::I_DISABLE;

            default:
                throw new InvalidArgumentException('Unknown enrolment allow type: ' . $type);
        }
    }

    public static function toString(int $type): string
    {
        switch ($type) {
            case self::I_DEFAULT:
                return self::DEFAULT;

            case self::I_ENQUIRY:
                return self::ENQUIRY;

            case self::I_DISABLE:
                return self::DISABLE;

            default:
                throw new InvalidArgumentException('Unknown enrolment allow type: ' . $type);
        }
    }
}
