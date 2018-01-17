<?php

namespace go1\util\tests;

use go1\util\Locale;

use PHPUnit\Framework\TestCase;

class LocaleTest extends TestCase
{
    public function testLanguageCode()
    {
        $this->assertEquals('nn', Locale::getLanguageCode('no'));
        $this->assertEquals('en', Locale::getLanguageCode('go1'));
    }
}
