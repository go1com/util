<?php

namespace go1\util;

use DateTime as DefaultDateTime;
use DateTimeZone;
use InvalidArgumentException;

class DateTime
{
    const DEFAULT_HUMAN_FORMAT    = 'Y-m-d H:i:s';
    const DATE_SHORT_MONTH_FORMAT = 'd-M-Y';
    const DATETIME_GREATER        = 1;
    const DATETIME_EQUAL          = 0;
    const DATETIME_LESS           = -1;

    public static function create($time, $timezone = 'UTC', $reset = false): DefaultDateTime
    {
        if (!$time) {
            throw new InvalidArgumentException('Specific date/time string can not empty');
        }

        $datetime = new DefaultDateTime;
        $datetime->setTimezone(new DateTimeZone($timezone));
        $datetime->setTimestamp((is_numeric($time) ? $time : strtotime($time)));
        $reset && $datetime->setTime(0, 0);

        return $datetime;
    }

    public static function atom($time, $format = DATE_ATOM, $timezone = 'UTC')
    {
        return static::create($time, $timezone)->format($format);
    }

    /**
     * @deprecated use ::atom()
     *
     * Returns date formatted according to the specified format and timezone
     *
     * @param string $time     A date/time string. Valid formats could be:
     *                         - 'now'
     *                         - '10 September 2000'
     *                         - '2016-12-30T10:01:33+0700'
     *                         - '1483067019' (Unix Timestamp)
     * @param string $format   Format accepted by date(). [optional]
     * @param string $timezone String representing the desired time zone. [optional]
     * @return string
     */
    public static function formatDate($time, $format = DATE_ISO8601, $timezone = 'UTC')
    {
        return static::create($time, $timezone)->format($format);
    }

    /**
     * Return comparision result of 2 datetime object
     *
     * @param $date1
     * @param $date2
     * @return integer
     *      * 1  if Greater than
     *      * 0  if Equal
     *      * -1 if Less than
     */
    public static function compare($date1, $date2): int
    {
        $d1 = static::create($date1);
        $d2 = static::create($date2);

        return ($d1 > $d2) ? static::DATETIME_GREATER : (($d1 < $d2) ? static::DATETIME_LESS : static::DATETIME_EQUAL);
    }
}
