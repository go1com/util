<?php

namespace go1\util;

use HTMLPurifier;
use stdClass;
use Traversable;

class Text
{
    /**
     * @param HTMLPurifier $html
     * @param mixed        $value
     */
    public static function purify(HTMLPurifier $html, &$value)
    {
        if (is_string($value)) {
            $value = $html->purify($value);
        }

        if (is_array($value) || ($value instanceof stdClass) || ($value instanceof Traversable)) {
            foreach ($value as $k => &$item) {
                static::purify($html, $item);
            }
        }
    }
}
