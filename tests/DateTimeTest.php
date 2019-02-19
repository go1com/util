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
        $this->assertEquals('2016-12-30T03:01:33+0000', DateTime::formatDate('2016-12-30T10:01:33+0700'));
        $this->assertEquals('2017-03-11T12:59:15+00:00', DateTime::atom(1489237155));
        $this->assertEquals('2016-12-30T03:01:33+00:00', DateTime::atom('2016-12-30T10:01:33+0700'));

        $this->assertTrue(DateTime::create(1489237155) instanceof DefaultDateTime);
        $this->assertTrue(DateTime::create('10 September 2000') instanceof DefaultDateTime);
        $this->assertTrue(DateTime::create('2016-12-30T10:01:33+0700') instanceof DefaultDateTime);
    }

    public function testCompare()
    {
        $this->assertEquals(DateTime::DATETIME_GREATER, DateTime::compare('now', '-1 week'));
        $this->assertEquals(DateTime::DATETIME_GREATER, DateTime::compare(time(), '1970-1-1'));
        $this->assertEquals(DateTime::DATETIME_GREATER, DateTime::compare(DateTime::formatDate('2017-7-12T09:01:33+0700'), DateTime::formatDate('2000-12-30T10:01:33+0700')));
        $this->assertEquals(DateTime::DATETIME_GREATER, DateTime::compare(DateTime::atom('2017-7-12T09:01:33+0700'), DateTime::atom('2000-12-30T10:01:33+0700')));
        $this->assertEquals(DateTime::DATETIME_EQUAL, DateTime::compare('now', 'now'));
        $this->assertEquals(DateTime::DATETIME_LESS, DateTime::compare('now', '+1 week'));
    }

    public function testResetTime()
    {
        $date1 = DateTime::create('2016-12-30T10:01:33+0000', 'UTC', true);
        $date2 = DateTime::create('2016-12-30T00:00:00+0000', 'UTC', false);
        $this->assertEquals($date1, $date2);
    }
}
