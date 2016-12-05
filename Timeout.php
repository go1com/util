<?php

namespace go1\util;

class Timeout
{
    public static function run(int $timeout, array $callbacks = [])
    {
        $start = time();
        foreach ($callbacks as &$callback) {
            call_user_func($callback);

            if (time() - $start > $timeout) {
                break;
            }
        }
    }

    public static function over(int $start, int $timeout): bool
    {
        return (strtotime('now', $start) - $start) >= $timeout;
    }
}
