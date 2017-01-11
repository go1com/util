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
        if (empty($time)) {
            throw new InvalidArgumentException('Specific date/time string can not empty');
        }

        if (!is_numeric($time) && strtotime($time)) {
            $tz = new DateTimeZone($timezone);
            $date = (new DefaultDateTime($time))->setTimezone($tz);

            return $date->format($format);
        }

        return date($format, $time);
    }
}
