<?php

namespace go1\util\tests;

use go1\util\Country;

use PHPUnit\Framework\TestCase;

class CountryTest extends TestCase
{
    public function testGetCountryName()
    {
        $this->assertEquals('Australia', Country::getName('AU'));
        $this->assertEquals('Vietnam', Country::getName('VN'));
    }
}
