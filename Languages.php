<?php

namespace go1\util;

class Languages
{
    const ALL = [
        'af'          => ['Afrikaans', 'Afrikaans'],
        'am'          => ['Amharic', 'አማርኛ'],
        'ar'          => ['Arabic', 'العربية', true],
        'ast'         => ['Asturian', 'Asturianu'],
        'az'          => ['Azerbaijani', 'Azərbaycanca'],
        'be'          => ['Belarusian', 'Беларуская'],
        'bg'          => ['Bulgarian', 'Български'],
        'bn'          => ['Bengali', 'বাংলা'],
        'bo'          => ['Tibetan', 'བོད་སྐད་'],
        'bs'          => ['Bosnian', 'Bosanski'],
        'ca'          => ['Catalan', 'Català'],
        'cs'          => ['Czech', 'Čeština'],
        'cy'          => ['Welsh', 'Cymraeg'],
        'da'          => ['Danish', 'Dansk'],
        'de'          => ['German', 'Deutsch'],
        'dz'          => ['Dzongkha', 'རྫོང་ཁ'],
        'el'          => ['Greek', 'Ελληνικά'],
        'en'          => ['English', 'English'],
        'en-au'       => ['English (AU)', 'English'],
        'en-uk'       => ['English (UK)', 'English'],
        'en-us'       => ['English (US)', 'English'],
        'en-x-simple' => ['Simple English', 'Simple English'],
        'eo'          => ['Esperanto', 'Esperanto'],
        'es'          => ['Spanish', 'Español'],
        'et'          => ['Estonian', 'Eesti'],
        'eu'          => ['Basque', 'Euskera'],
        'fa'          => ['Persian, Farsi', 'فارسی', true],
        'fi'          => ['Finnish', 'Suomi'],
        'fil'         => ['Filipino', 'Filipino'],
        'fo'          => ['Faeroese', 'Føroyskt'],
        'fr'          => ['French', 'Français'],
        'fy'          => ['Frisian, Western', 'Frysk'],
        'ga'          => ['Irish', 'Gaeilge'],
        'gd'          => ['Scots Gaelic', 'Gàidhlig'],
        'gl'          => ['Galician', 'Galego'],
        'gsw-berne'   => ['Swiss German', 'Schwyzerdütsch'],
        'gu'          => ['Gujarati', 'ગુજરાતી'],
        'he'          => ['Hebrew', 'עברית', true],
        'hi'          => ['Hindi', 'हिन्दी'],
        'hr'          => ['Croatian', 'Hrvatski'],
        'ht'          => ['Haitian Creole', 'Kreyòl ayisyen'],
        'hu'          => ['Hungarian', 'Magyar'],
        'hy'          => ['Armenian', 'Հայերեն'],
        'id'          => ['Indonesian', 'Bahasa Indonesia'],
        'is'          => ['Icelandic', 'Íslenska'],
        'it'          => ['Italian', 'Italiano'],
        'ja'          => ['Japanese', '日本語'],
        'jv'          => ['Javanese', 'Basa Java'],
        'ka'          => ['Georgian', 'ქართული ენა'],
        'kk'          => ['Kazakh', 'Қазақ'],
        'km'          => ['Khmer', 'ភាសាខ្មែរ'],
        'kn'          => ['Kannada', 'ಕನ್ನಡ'],
        'ko'          => ['Korean', '한국어'],
        'ku'          => ['Kurdish', 'Kurdî'],
        'ky'          => ['Kyrgyz', 'Кыргызча'],
        'lo'          => ['Lao', 'ພາສາລາວ'],
        'lt'          => ['Lithuanian', 'Lietuvių'],
        'lv'          => ['Latvian', 'Latviešu'],
        'mg'          => ['Malagasy', 'Malagasy'],
        'mk'          => ['Macedonian', 'Македонски'],
        'ml'          => ['Malayalam', 'മലയാളം'],
        'mn'          => ['Mongolian', 'монгол'],
        'mr'          => ['Marathi', 'मराठी'],
        'ms'          => ['Bahasa Malaysia', 'بهاس ملايو'],
        'my'          => ['Burmese', 'ဗမာစကား'],
        'ne'          => ['Nepali', 'नेपाली'],
        'nl'          => ['Dutch', 'Nederlands'],
        'nb'          => ['Norwegian Bokmål', 'Norsk, bokmål'],
        'nn'          => ['Norwegian Nynorsk', 'Norsk, nynorsk'],
        'oc'          => ['Occitan', 'Occitan'],
        'pa'          => ['Punjabi', 'ਪੰਜਾਬੀ'],
        'pl'          => ['Polish', 'Polski'],
        'pt-pt'       => ['Portuguese, Portugal', 'Português, Portugal'],
        'pt-br'       => ['Portuguese, Brazil', 'Português, Brasil'],
        'ro'          => ['Romanian', 'Română'],
        'ru'          => ['Russian', 'Русский'],
        'sco'         => ['Scots', 'Scots'],
        'se'          => ['Northern Sami', 'Sámi'],
        'si'          => ['Sinhala', 'සිංහල'],
        'sk'          => ['Slovak', 'Slovenčina'],
        'sl'          => ['Slovenian', 'Slovenščina'],
        'sq'          => ['Albanian', 'Shqip'],
        'sr'          => ['Serbian', 'Српски'],
        'sv'          => ['Swedish', 'Svenska'],
        'sw'          => ['Swahili', 'Kiswahili'],
        'ta'          => ['Tamil', 'தமிழ்'],
        'ta-lk'       => ['Tamil, Sri Lanka', 'தமிழ், இலங்கை'],
        'te'          => ['Telugu', 'తెలుగు'],
        'th'          => ['Thai', 'ภาษาไทย'],
        'tr'          => ['Turkish', 'Türkçe'],
        'tyv'         => ['Tuvan', 'Тыва дыл'],
        'ug'          => ['Uyghur', 'ئۇيغۇرچە', true],
        'uk'          => ['Ukrainian', 'Українська'],
        'ur'          => ['Urdu', 'اردو', true],
        'vi'          => ['Vietnamese', 'Tiếng Việt'],
        'xx-lolspeak' => ['Lolspeak', 'Lolspeak'],
        'zh-hans'     => ['Chinese, Simplified', '简体中文'],
        'zh-hant'     => ['Chinese, Traditional', '繁體中文'],
    ];

    // last updated: 2019-05-20
    const ISO_639_1_CODES = [
        'aa',
        'ab',
        'ae',
        'af',
        'ak',
        'am',
        'an',
        'ar',
        'as',
        'av',
        'ay',
        'az',
        'ba',
        'be',
        'bg',
        'bh',
        'bi',
        'bm',
        'bn',
        'bo',
        'br',
        'bs',
        'ca',
        'ce',
        'ch',
        'co',
        'cr',
        'cs',
        'cu',
        'cv',
        'cy',
        'da',
        'de',
        'dv',
        'dz',
        'ee',
        'el',
        'en',
        'eo',
        'es',
        'et',
        'eu',
        'fa',
        'ff',
        'fi',
        'fj',
        'fo',
        'fr',
        'fy',
        'ga',
        'gd',
        'gl',
        'gn',
        'gu',
        'gv',
        'ha',
        'he',
        'hi',
        'ho',
        'hr',
        'ht',
        'hu',
        'hy',
        'hz',
        'ia',
        'id',
        'ie',
        'ig',
        'ii',
        'ik',
        'io',
        'is',
        'it',
        'iu',
        'ja',
        'jv',
        'ka',
        'kg',
        'ki',
        'kj',
        'kk',
        'kl',
        'km',
        'kn',
        'ko',
        'kr',
        'ks',
        'ku',
        'kv',
        'kw',
        'ky',
        'la',
        'lb',
        'lg',
        'li',
        'ln',
        'lo',
        'lt',
        'lu',
        'lv',
        'mg',
        'mh',
        'mi',
        'mk',
        'ml',
        'mn',
        'mr',
        'ms',
        'mt',
        'my',
        'na',
        'nb',
        'nd',
        'ne',
        'ng',
        'nl',
        'nn',
        'no',
        'nr',
        'nv',
        'ny',
        'oc',
        'oj',
        'om',
        'or',
        'os',
        'pa',
        'pi',
        'pl',
        'ps',
        'pt',
        'qu',
        'rm',
        'rn',
        'ro',
        'ru',
        'rw',
        'sa',
        'sc',
        'sd',
        'se',
        'sg',
        'si',
        'sk',
        'sl',
        'sm',
        'sn',
        'so',
        'sq',
        'sr',
        'ss',
        'st',
        'su',
        'sv',
        'sw',
        'ta',
        'te',
        'tg',
        'th',
        'ti',
        'tk',
        'tl',
        'tn',
        'to',
        'tr',
        'ts',
        'tt',
        'tw',
        'ty',
        'ug',
        'uk',
        'ur',
        'uz',
        've',
        'vi',
        'vo',
        'wa',
        'wo',
        'xh',
        'yi',
        'yo',
        'za',
        'zh',
        'zu',
    ];

    const LOCALE_TO_LANGUAGE = [
        'au'    => 'en-AU',
        'en-au' => 'en-AU',
        'en-us' => 'en',
        'mx'    => 'es',
        'pt-pt' => 'pt-PT',
        'br'    => 'pt-BR',
        'vi'    => 'vi',
        'no'    => 'nn',
        'nn'    => 'nn',
    ];

    const COUNTRY_TO_LOCALE = [
        'AU' => 'en-au',
        'US' => 'en-us',
        'PT' => 'pt-pt',
        'BR' => 'pt-br',
        'DE' => 'de',
        'ES' => 'es',
        'MX' => 'es',
        'VN' => 'vi',
        'NO' => 'no',
    ];

    public static function codes(): array
    {
        return array_keys(static::ALL);
    }

    public static function getLanguageCode($locale, $default = 'en')
    {
        return self::LOCALE_TO_LANGUAGE[$locale] ?? $default;
    }
}
