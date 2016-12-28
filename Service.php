<?php

namespace go1\util;

class Service
{
    public static function urls(array $names, string $env, string $pattern = 'http://SERVICE.ENVIRONMENT.go1.service'): array
    {
        foreach ($names as $name) {
            $urls["{$name}_url"] = static::url($names, $env, $pattern);
        }

        return !empty($urls) ? $urls : [];
    }

    public static function url(string $name, string $env, string $pattern = 'http://SERVICE.ENVIRONMENT.go1.service'): string
    {
        return str_replace(['SERVICE', 'ENVIRONMENT'], [$name, $env], $pattern);
    }
}
