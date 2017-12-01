<?php

namespace go1\util;

class Image
{
    public static function scale(string $url, int $width = 0, int $height = 0): string
    {
        if ($width || $height) {
            $service = 'https://res.cloudinary.com/go1/image/fetch/%resolution/';
            if ($width && $height) {
                $service = str_replace('%resolution', "w_{$width},h_{$height}", $service);
            }
            else if ($width) {
                $service = str_replace('%resolution', "w_{$width}", $service);
            }
            else if ($height) {
                $service = str_replace('%resolution', "h_{$height}", $service);
            }

            return $service . $url;
        }

        return $url;
    }
}
