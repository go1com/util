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
        $this->assertEquals('nn', Languages::getLanguageCode('nn'));
        $this->assertEquals('es', Languages::getLanguageCode('mx'));
        $this->assertEquals('pt-BR', Languages::getLanguageCode('br'));
    }
}
