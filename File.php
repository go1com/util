<?php

namespace go1\util;

use Behat\Transliterator\Transliterator;
use Mimey\MimeTypes;

class File
{
    public static function fileName(string $fileName)
    {
        return Transliterator::transliterate($fileName);
    }

    public static function fileMimeType(string $fileName)
    {
        $ext = pathinfo($fileName, PATHINFO_EXTENSION);
        return (new MimeTypes())->getMimeType($ext);
    }
}
