<?php

namespace go1\util;

use Assert\Assert;
use Assert\LazyAssertionException;
use Behat\Transliterator\Transliterator;
use Firebase\JWT\JWT;
use HTMLPurifier;
use HTMLPurifier_Config;
use stdClass;
use Traversable;

class Text
{
    public static function defaultPurifier(): HTMLPurifier
    {
        $cnf = HTMLPurifier_Config::createDefault();
        $cnf->set('Cache.DefinitionImpl', null);

        return new HTMLPurifier($cnf);
    }

    /**
     * @param HTMLPurifier $html
     * @param mixed        $value
     */
    public static function purify(HTMLPurifier $html = null, &$value, HTMLPurifier_Config $config = null)
    {
        $html = $html ?: self::defaultPurifier();

        if (is_string($value)) {
            $value = $html->purify($value, $config);
        }

        if (is_array($value) || ($value instanceof stdClass) || ($value instanceof Traversable)) {
            foreach ($value as $k => &$item) {
                static::purify($html, $item, $config);
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

    public static function parseInlineTags(string $string)
    {
        return array_filter(explode('] [', $string));
    }

    public static function toSnakeCase($input)
    {
        preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $input, $matches);
        $ret = $matches[0];
        $result = [];
        foreach ($ret as &$match) {
            // lower all words if all words are upper, else lower first character for Datatable filter
            $result[] = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
        }

        return implode('_', $result);
    }

    public static function fileName(string $fileName)
    {
        return Transliterator::transliterate($fileName, '-');
    }
}
