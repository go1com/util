<?php

namespace go1\util;

use Assert\Assert;
use Assert\LazyAssertionException;
use Firebase\JWT\JWT;
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

    public static function isEmail(string $string)
    {
        try {
            Assert::lazy()
                  ->that($string, 'string')->email()
                  ->verifyNow();

            return true;
        }
        catch (LazyAssertionException $e) {
            return false;
        }
    }

    public static function jwtContent(string $jwt, $i = 1)
    {
        $payload = JWT::urlsafeB64Decode(explode('.', $jwt)[$i]);

        return Jwt::jsonDecode($payload);
    }
}
