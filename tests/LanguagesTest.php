<?php

namespace go1\util\tests;

use go1\util\Languages;

use PHPUnit\Framework\TestCase;

class LanguagesTest extends TestCase
{
    public function testLanguageCode()
    {
        $this->assertEquals('en', Languages::getLanguageCode('go1'));
        $this->assertEquals('nn', Languages::getLanguageCode('no'));
        $this->assertEquals('es', Languages::getLanguageCode('mx'));
        $this->assertEquals('pt-Br', Languages::getLanguageCode('br'));
    }
}
