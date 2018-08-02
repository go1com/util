<?php

namespace go1\util\tests;

use go1\util\Base58;
use PHPUnit\Framework\TestCase;

class Base58Test extends TestCase
{
    /**
     * @dataProvider dataProvider
     */
    public function testEncode($string, $encoded)
    {
        $string = (string) $string;
        $encoded = (string) $encoded;
        $this->assertSame($encoded, Base58::encode($string));
    }

    /**
     * @dataProvider dataProvider
     */
    public function testDecode($string, $encoded)
    {
        $string = (string) $string;
        $encoded = (string) $encoded;
        $this->assertSame($string, Base58::decode($encoded));
    }

    public function dataProvider()
    {
        $tests = [
            ['', ''],
            ['1', 'r'],
            ['123456', 'RVu1HWU5'],
            ['Hello World', 'JxF12TrwUP45BMd'],
            ['Hello World!', '2NEpo7TZRRrLZSi2U'],
            ["\x00\x61", '12g'],
            ["\x00", '1'],
            ["\x00\x00", '11'],
            [
                '0248ac9d3652ccd8350412b83cb08509e7e4bd41',
                '3PtvAWwSMPe2DohNuCFYy76JhMV3rhxiSxQMbPBTtiPvYvneWu95XaY',
            ],
        ];
        $return = [];
        foreach ($tests as $test) {
            $return[] = $test;
        }

        return $return;
    }
}
