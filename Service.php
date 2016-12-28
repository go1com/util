<?php

namespace go1\util;

class Service
{
    public static function url(string $name, string $env, string $pattern = 'http://SERVICE.ENVIRONMENT.go1.service'): string
    {
        return str_replace(['SERVICE', 'ENVIRONMENT'], [$name, $env], $pattern);
    }
}
