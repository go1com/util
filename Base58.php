<?php

namespace go1\util;

use InvalidArgumentException;

/**
 * The Base58 value encoding/decoding class. The Base58 encoding allows arbitrary
 * data to be encoded using only alphanumeric characters.
 *
 * Inspired by base58php of Stephen Hill.
 *
 * @see https://en.bitcoin.it/wiki/Base58Check_encoding
 */
class Base58
{
    // All alphanumeric characters except for "0", "I", "O", and "l".
    // @see https://en.wikipedia.org/wiki/Base58
    static $alphabet = '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz';

    const BASE = 58;

    public static function encode(string $string): string
    {
        if (strlen($string) === 0) {
            return '';
        }
        if (function_exists('\gmp_init') === true) {
            // Convert the byte array into an arbitrary-precision decimal
            // Performing a base256 to base10 conversion
            $hex = unpack('H*', $string);
            $hex = reset($hex);
            $decimal = gmp_init($hex, 16);

            // Performs base 10 to base 58 conversion
            $output = '';
            while (gmp_cmp($decimal, self::BASE) >= 0) {
                list($decimal, $mod) = gmp_div_qr($decimal, self::BASE);
                $output .= self::$alphabet[gmp_intval($mod)];
            }
            if (gmp_cmp($decimal, 0) > 0) {
                $output .= self::$alphabet[gmp_intval($decimal)];
            }
            $output = strrev($output);
            $bytes = str_split($string);
            foreach ($bytes as $byte) {
                if ($byte === "\x00") {
                    $output = self::$alphabet[0] . $output;
                    continue;
                }
                break;
            }

            return $output;
        }
        else {
            throw new \Exception('Please install the GMP extension.');
        }
    }

    public static function decode(string $base58): string
    {
        if (strlen($base58) === 0) {
            return '';
        }
        if (function_exists('\gmp_init') === true) {
            $indexes = array_flip(str_split(self::$alphabet));
            $chars = str_split($base58);

            // Check invalid characters
            foreach ($chars as $char) {
                if (isset($indexes[$char]) === false) {
                    throw new InvalidArgumentException('Argument $base58 contains invalid characters.');
                }
            }

            // Convert from base58 to base10
            $decimal = gmp_init($indexes[$chars[0]], 10);

            for ($i = 1, $l = count($chars); $i < $l; $i++) {
                $decimal = gmp_mul($decimal, self::BASE);
                $decimal = gmp_add($decimal, $indexes[$chars[$i]]);
            }

            // Convert from base10 to base256
            $output = '';
            while (gmp_cmp($decimal, 0) > 0) {
                list($decimal, $byte) = gmp_div_qr($decimal, 256);
                $output = pack('C', gmp_intval($byte)) . $output;
            }

            foreach ($chars as $char) {
                if ($indexes[$char] === 0) {
                    $output = "\x00" . $output;
                    continue;
                }
                break;
            }

            return $output;
        }
        else {
            throw new \Exception('Please install the GMP extension.');
        }
    }
}
