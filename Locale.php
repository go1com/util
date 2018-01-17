<?php

namespace go1\util;

class Locale
{
    const LOCALE_TO_LANGUAGE = [
        'en-au' => 'en-AU',
        'en-us' => 'en',
        'pt-pt' => 'pt-PT',
        'vi'    => 'vi',
        'no'    => 'nn',
    ];

    const COUNTRY_TO_LOCALE = [
        'AU' => 'en-au',
        'US' => 'en-us',
        'PT' => 'pt-pt',
        'DE' => 'de',
        'ES' => 'es',
        'VN' => 'vi',
        'NO' => 'no',
    ];

    public static function getLanguageCode($locale, $default = 'en')
    {
        return self::LOCALE_TO_LANGUAGE[$locale] ?? $default;
    }
}
