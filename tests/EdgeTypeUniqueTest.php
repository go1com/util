<?php

namespace go1\util\tests;

use go1\util\EdgeTypes;
use PHPUnit_Framework_TestCase;
use ReflectionClass;

class EdgeTypeUniqueTest extends PHPUnit_Framework_TestCase
{
    public function test()
    {
        $rClass = new ReflectionClass(EdgeTypes::class);

        $values = [];
        foreach ($rClass->getConstants() as $key => $value) {
            if (is_scalar($value)) {
                $this->assertNotContains($value, $values, "Duplication: {$key}");
                $values[] = $value;
            }
        }
    }
}
