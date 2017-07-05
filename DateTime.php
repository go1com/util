<?php

namespace go1\util;

use DateTime as DefaultDateTime;
use DateTimeZone;
use InvalidArgumentException;

/**
 * DateTime Helper class
 */
class DateTime
{
    const DEFAULT_TO_STRING_FORMAT = 'Y-m-d H:i:s';

    public static function create($time, $timezone = 'UTC'): DefaultDateTime
    {
        if (!$time) {
            throw new InvalidArgumentException('Specific date/time string can not empty');
        }

        $datetime = new DefaultDateTime;
        $datetime->setTimezone(new DateTimeZone($timezone));
        $datetime->setTimestamp((is_numeric($time) ? $time : strtotime($time)));

        return $datetime;
    }

    /**
     * Returns date formatted according to the specified format and timezone
     * @param string $time  A date/time string. Valid formats could be:
     * - 'now'
     * - '10 September 2000'
     * - '2016-12-30T10:01:33+0700'
     * - '1483067019' (Unix Timestamp)
     * @param string $format Format accepted by date(). [optional]
     * @param string $timezone String representing the desired time zone. [optional]
     * @return string
     */
    public static function formatDate($time, $format = DATE_ISO8601, $timezone = 'UTC') {
        return static::create($time, $timezone)->format($format);
    }
}
