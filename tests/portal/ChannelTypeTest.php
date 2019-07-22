<?php
namespace go1\util\tests\portal;

use go1\util\portal\ChannelType;
use PHPUnit\Framework\TestCase;

class ChannelTypeTest extends TestCase
{
    public function testAll()
    {
        $channelTypes = ChannelType::all();
        $this->assertEmpty(array_diff_assoc([
            'internal',
            'referral_partner',
            'distribution_partner',
            'sales',
            'existing_customer',
            'direct',
            'platform_partner',
            'portal_launcher',
        ], $channelTypes));
    }

    /**
     * @dataProvider typeToStringProvider
     */
    public function testToString($type, $string)
    {
        $this->assertEquals($string, ChannelType::toString($type));
    }

    public function testToStringInvalidType()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown channel type: foobar');
        ChannelType::toString('foobar');
    }

    public function typeToStringProvider()
    {
        return [
            ['sales' , 'SDR / Account Exec'],
            ['internal' , 'Internal'],
            ['direct' , 'Direct or Inbound'],
            ['portal_launcher' , 'Portal Launcher'],
        ];
    }
}
