<?php

namespace go1\util\tests;

use DateTime as DefaultDateTime;
use go1\util\DateTime;
use PHPUnit\Framework\TestCase;

class DateTimeTest extends TestCase
{
    public function test()
    {
        $this->assertEquals('2017-03-11T12:59:15+0000', DateTime::formatDate(1489237155));
        $this->assertEquals('2000-09-10T00:00:00+0000', DateTime::formatDate('10 September 2000'));
        $this->assertEquals('2016-12-30T03:01:33+0000', DateTime::formatDate('2016-12-30T10:01:33+0700'));

        $this->assertTrue(DateTime::create(1489237155) instanceof DefaultDateTime);
        $this->assertTrue(DateTime::create('10 September 2000') instanceof DefaultDateTime);
        $this->assertTrue(DateTime::create('2016-12-30T10:01:33+0700') instanceof DefaultDateTime);
    }
}
