<?php

namespace go1\util\schema\tests;

use go1\util\text\Xss;
use PHPUnit\Framework\TestCase;

class XssTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
    }

    public function dataFilter()
    {
        return [
            ['c:/Temp/ほげほげ'],
            ['你好'],
            ['Žluťoučký kůň\n'],
            ['test mấy cái liên quan tới string'],
            ['1CD-Trung & V43-Level...'],
            ["^'£$%^&*()}{@'#~?><>,@|-=-_+-¬'"],
            ['foo<'],
        ];
    }

    /**
     * @dataProvider dataFilter
     */
    public function testFilter($text)
    {
        $filterText = Xss::filter($text);

        $this->assertEquals($text, html_entity_decode($filterText));
    }
}
