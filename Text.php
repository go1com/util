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
    # 47 === JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_NUMERIC_CHECK
    # https://github.com/symfony/http-foundation/blob/master/JsonResponse.php#L31
    const JSON_ENCODING_OPTIONS = 47;

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
        preg_match_all('/\[+([^[]+)\]/', $string, $tags);
        $tags = array_map(function ($tag) {
            return trim(preg_replace(['/\[/', '/\]/'], '', $tag, 1));
        }, $tags[0]);

        return array_filter($tags);
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

    public static function hmacBase64($data, $key)
    {
        return strtr(
            base64_encode(hash_hmac('sha256', (string) $data, (string) $key, true)),
            ['+' => '-', '/' => '_', '=' => '']
        );
    }

    /**
     * Follow uuid v4 generation standard but remove dash and encode in base 32
     * Guarantee 128 bits of entropy but use ~ 20% less space compare to base 16
     *
     * @see https://stackoverflow.com/a/15875555
     * @see https://connect2id.com/blog/how-to-generate-human-friendly-identifiers
     */
    public static function uniqueId(): string
    {
        $rand = random_bytes(16);
        $rand[6] = chr(ord($rand[6]) & 0x0f | 0x40);
        $rand[8] = chr(ord($rand[8]) & 0x3f | 0x80);
        $rand = str_split(bin2hex($rand), 4);

        return vsprintf('%s%s%s%s%s', array_map(function ($hex) {
            return base_convert($hex, 16, 32);
        }, [$rand[0] . $rand[1], $rand[2], $rand[3], $rand[4], $rand[5] . $rand[6] . $rand[7]]));
    }
}
