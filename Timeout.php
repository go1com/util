<?php

namespace go1\util;

class Timeout
{
    public static function run(int $seconds, array $callbacks = [])
    {
        $time = time();
        foreach ($callbacks as &$callback) {
            call_user_func($callback);

            $now = time();
            if ($now - $time > $seconds) {
                break;
            }
            else {
                $time = $now;
            }
        }
    }
}
